<?php

namespace App\Http\Livewire;

use App\Models\DetalleFactura;
use App\Models\Factura;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\User;
use DateTime;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Spatie\Permission\Models\Role;
use App\Models\PaymentMethod;

class Dashboard extends Component
{

    public $year, $salesByMonth_Data = [], $top5Data =[], $weekSales_Data=[], $listYears=[], $salesByPaymentMethod_Data = [];
    public $tenantid;
    public $modalOpen = false;

    public function openModal()
    {
        $this->modalOpen = true;
    }

    public function closeModal()
    {
        $this->modalOpen = false;
    }
    public function mount()
    {
        if ($this->year == '') $this->year = date('Y');
        
    }


    public function render()
    {
        $this->getTenantId() ;

        $this->listYears =[];
        $currentYear =  date('Y') -2;
        for ($i=1; $i < 7 ; $i++) {
            array_push($this->listYears, $currentYear +$i);
        }

        $this->getTop5();
        $this->getWeekSales();
        $this->getSalesMonth();
        $this->getSalesByPaymentMethod();

        return view('livewire.dash.component')->layout('layouts.theme.app');
    }

    public function getTenantId()
    {
        $this->tenantid = auth()->user()->tenant_id;
        
    }

    public function getTop5 ()
    {

        $this->top5Data = DetalleFactura::join('products as p', 'detalle_facturas.product_id','p.id')
        ->join('facturas', 'detalle_facturas.factura_id', '=', 'facturas.id')
        ->select(
            DB::raw("p.name as product, sum(detalle_facturas.cantidad * p.price) as total")
        )->whereYear('detalle_facturas.created_at', $this->year)
        ->whereNull('facturas.deleted_at')
        ->whereNotNull('facturas.numeroAutorizacion')
        ->groupBy('p.name')
        ->orderBy(DB::raw("sum(detalle_facturas.cantidad * p.price)"), 'desc')
        ->get()->take(5)->toArray();

        $contDif = ( 5 - count($this->top5Data)); // si la consulta devuelve 5 productos o menos

        if ($contDif > 0) {
            for ($i=1; $i <= $contDif; $i++) {
                array_push($this->top5Data, ["product" => "-", "total" => 0]);
            }
        }

    }

    public function getWeekSales()
    {
        $dt = new DateTime();
        $dates = [];
        $startDate = null;
        $finishDate = null;
        $this->weekSales_Data = [];

        for ($d = 1; $d <= 7; $d++) {

            $dt->setISODate($dt->format('o'), $dt->format('W'), $d);
            $dates[$dt->format('dd')] = $dt->format('m-d-Y');

            $startDate = $dt->format('Y-m-d') . ' 00:00:00';
            $finishDate = $dt->format('Y-m-d') . ' 23:59:59';

            //replace year
            $startDate = substr_replace($startDate, $this->year, 0, 4);
            $finishDate = substr_replace($finishDate, $this->year, 0, 4);


            $wsale = Factura::whereBetween('created_at', [$startDate, $finishDate])
                ->whereNotNull('numeroAutorizacion')
                ->sum('total');

            array_push($this->weekSales_Data, $wsale);

        }

    }


    public function getSalesMonth()
    {
        $this->salesByMonth_Data = [];

        $salesByMonth = DB::select(
            DB::raw("SELECT coalesce(total,0)as total
                FROM (SELECT 'january' AS month UNION SELECT 'february' AS month UNION SELECT 'march'
                AS month UNION SELECT 'april' AS month UNION SELECT 'may'
                AS month UNION SELECT 'june' AS month UNION SELECT 'july' AS month UNION SELECT 'august'
                 AS month UNION SELECT 'september' AS month UNION SELECT 'october'
                  AS month UNION SELECT 'november' AS month UNION SELECT 'december' AS month )
                  m LEFT JOIN (SELECT MONTHNAME(created_at) AS MONTH, COUNT(*) AS orders, SUM(total)AS total
                FROM facturas WHERE year(created_at)= $this->year
                AND deleted_at IS NULL
                AND numeroAutorizacion IS NOT NULL
                GROUP BY MONTHNAME(created_at),MONTH(created_at)
                ORDER BY MONTH(created_at)) c ON m.MONTH =c.MONTH;")
        );

        foreach ($salesByMonth as $sale) {
            array_push($this->salesByMonth_Data, $sale->total);
        }

    }

    public function getSalesByPaymentMethod()
    {
        $this->salesByPaymentMethod_Data = Factura::join('payment_methods', 'facturas.formaPago', '=', 'payment_methods.code')
            ->select(
                DB::raw("payment_methods.description as method, sum(facturas.total) as total")
            )
            ->whereYear('facturas.created_at', $this->year)
            ->whereNotNull('facturas.numeroAutorizacion')
            ->groupBy('payment_methods.description')
            ->get()->toArray();
    }

    // ecuchar cuadno la propiedad year se actualice
    public function updatedYear()
    {
        $this->dispatchBrowserEvent('reload-scripts');
    }
}

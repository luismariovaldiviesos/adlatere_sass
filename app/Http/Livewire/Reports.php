<?php

namespace App\Http\Livewire;

use App\Models\User;
use Livewire\Component;
use Livewire\WithPagination;
use Carbon\Carbon;

class Reports extends Component
{

    use WithPagination;

    public $search, $startDate, $endDate, $userId= 'TODOS', $details = [];
    private $pagination = 100;

    public $casosActivos = 0;
    public $audienciasPeriodo = 0;
    public $actuacionesPeriodo = 0;
    public $tareasPendientes = 0;
    public $firmasPendientes = 0;
    public $periodoLabel = '';
    public $cargaAbogados = [];
    public $proximasAudiencias = [];
    public $tareasCriticas = [];

    public function mount()
    {
        if (!$this->startDate) {
            $this->startDate = Carbon::now()->startOfMonth()->format('Y-m-d');
        }
        if (!$this->endDate) {
            $this->endDate = Carbon::now()->format('Y-m-d');
        }
    }

    public function render()
    {
        $query = $this->getQuery();

        $from = Carbon::parse($this->startDate)->startOfDay();
        $to = Carbon::parse($this->endDate)->endOfDay();

        $this->casosActivos = \App\Models\Juicio::count();
        $this->audienciasPeriodo = \App\Models\Audiencia::whereBetween('fecha_hora', [$from, $to])->count();
        $this->actuacionesPeriodo = \App\Models\Actividad::whereBetween('fecha_actividad', [$from, $to])->count();
        $this->tareasPendientes = \App\Models\Tarea::whereIn('estado', ['pendiente', 'en_proceso'])->count();
        $this->firmasPendientes = \App\Models\Actividad::where('estado_firma', 'pendiente')->count();
        $this->periodoLabel = $from->format('d/m/Y') . ' al ' . $to->format('d/m/Y');

        $abogados = User::where('profile', 'Abogado')
            ->where('status', 'ACTIVE')
            ->orderBy('name')
            ->get();

        if ($this->userId !== 'TODOS' && str_contains($this->userId, '|')) {
            $uid = (int) trim(explode('|', $this->userId)[1]);
            $abogados = $abogados->where('id', $uid);
        }

        $this->cargaAbogados = $abogados->map(function ($abogado) use ($from, $to) {
            return [
                'id' => $abogado->id,
                'name' => $abogado->name,
                'casos' => \App\Models\Juicio::whereHas('abogados', fn ($q) => $q->where('user_id', $abogado->id))->count(),
                'audiencias' => \App\Models\Audiencia::whereHas('juicio.abogados', fn ($q) => $q->where('user_id', $abogado->id))
                    ->whereBetween('fecha_hora', [$from, $to])->count(),
                'actuaciones' => \App\Models\Actividad::where('user_id', $abogado->id)
                    ->whereBetween('fecha_actividad', [$from, $to])->count(),
                'tareas' => \App\Models\Tarea::where('asignado_a', $abogado->id)
                    ->whereIn('estado', ['pendiente', 'en_proceso'])->count(),
            ];
        })->values()->toArray();

        $this->proximasAudiencias = \App\Models\Audiencia::with(['juicio:id,cod_satje'])
            ->where('estado', 'Programada')
            ->whereBetween('fecha_hora', [now(), now()->copy()->addDays(14)])
            ->orderBy('fecha_hora')
            ->limit(8)
            ->get();

        $this->tareasCriticas = \App\Models\Tarea::with(['asignado:id,name', 'juicio:id,cod_satje'])
            ->whereIn('estado', ['pendiente', 'en_proceso'])
            ->whereNotNull('fecha_limite')
            ->whereDate('fecha_limite', '<=', now()->copy()->addDays(7))
            ->orderBy('fecha_limite')
            ->limit(8)
            ->get();

        
        // Calcular Ventas Netas (Restando Notas de Crédito '04')
        $totalSales = $query->clone()->selectRaw("SUM(CASE WHEN codDoc = '04' THEN -total ELSE total END) as net_total")->value('net_total');
        
        // Desglose
        $sumFacturas = $query->clone()->where('codDoc', '01')->sum('total');
        $sumNotasCredito = $query->clone()->where('codDoc', '04')->sum('total');
        
        // Calcular items netos
        $totalItems = $query->clone()->join('detalle_facturas', 'facturas.id', '=', 'detalle_facturas.factura_id')
                            ->selectRaw("SUM(CASE WHEN facturas.codDoc = '04' THEN -detalle_facturas.cantidad ELSE detalle_facturas.cantidad END) as net_qty")
                            ->value('net_qty');

        // Agrupar por método de pago (Neto)
        $totalByPaymentMethod = $query->clone()
            ->join('payment_methods', 'facturas.formaPago', '=', 'payment_methods.code')
            ->selectRaw('payment_methods.description as method, sum(CASE WHEN facturas.codDoc = "04" THEN -facturas.total ELSE facturas.total END) as total')
            ->groupBy('payment_methods.description')
            ->get();

        $orders = $query->with(['usuario', 'customer', 'detalles', 'paymentMethod', 'facturaModificada'])
                        ->orderBy('id', 'desc')
                        ->paginate($this->pagination);

        return view('livewire.reports.component', [
            'users' => $this->loadUsers(),
            'orders' => $orders,
            'totalSales' => $totalSales,
            'sumFacturas' => $sumFacturas,
            'sumNotasCredito' => $sumNotasCredito,
            'totalItems' => $totalItems,
            'totalByPaymentMethod' => $totalByPaymentMethod
        ])
        ->layout('layouts.theme.app');
    }

    public function ReportPDF()
    {
        $query = $this->getQuery();
        
        // Calcular totales Netos
        $totalSales = $query->clone()->selectRaw("SUM(CASE WHEN codDoc = '04' THEN -total ELSE total END) as net_total")->value('net_total');
        
        $sumFacturas = $query->clone()->where('codDoc', '01')->sum('total');
        $sumNotasCredito = $query->clone()->where('codDoc', '04')->sum('total');
        
        $totalItems = $query->clone()->join('detalle_facturas', 'facturas.id', '=', 'detalle_facturas.factura_id')
                            ->selectRaw("SUM(CASE WHEN facturas.codDoc = '04' THEN -detalle_facturas.cantidad ELSE detalle_facturas.cantidad END) as net_qty")
                            ->value('net_qty');

        $totalByPaymentMethod = $query->clone()
            ->join('payment_methods', 'facturas.formaPago', '=', 'payment_methods.code')
            ->selectRaw('payment_methods.description as method, sum(CASE WHEN facturas.codDoc = "04" THEN -facturas.total ELSE facturas.total END) as total')
            ->groupBy('payment_methods.description')
            ->get();

        $data = $query->with(['usuario', 'customer', 'detalles', 'paymentMethod', 'facturaModificada'])
                      ->orderBy('id', 'desc')
                      ->get();

        $pdf = \PDF::loadView('livewire.reports.sales-pdf', [
            'orders' => $data,
            'user' => $this->userId == 'TODOS' ? 'Todos' : explode('|', $this->userId)[0],
            'startDate' => $this->startDate,
            'endDate' => $this->endDate,
            'endDate' => $this->endDate,
            'totalSales' => $totalSales,
            'sumFacturas' => $sumFacturas,
            'sumNotasCredito' => $sumNotasCredito,
            'totalItems' => $totalItems,
            'totalByPaymentMethod' => $totalByPaymentMethod
        ]);

        return response()->streamDownload(function () use ($pdf) {
            echo $pdf->output();
        }, 'Reporte_Ventas.pdf');
    }

    public function getReport()
    {
        // Método puente
    }

    public function getQuery()
    {
        if ($this->startDate == '' || $this->endDate == '') {
            $from = Carbon::now()->format('Y-m-d') . ' 00:00:00';
            $to = Carbon::now()->format('Y-m-d') . ' 23:59:59';
        } else {
            $from = Carbon::parse($this->startDate)->format('Y-m-d') . ' 00:00:00';
            $to   = Carbon::parse($this->endDate)->format('Y-m-d') . ' 23:59:59';
        }

        $query = \App\Models\Factura::whereBetween('facturas.created_at', [$from, $to])
            ->whereNotNull('numeroAutorizacion');

        if ($this->userId != 'TODOS'){
            if (str_contains($this->userId, '|')) {
                $uid = trim(explode("|", $this->userId)[1]);
                $query->where('user_id', $uid);
            }
        }
        
        return $query;
    }
    public function loadUsers()
    {
        if(strlen($this->search))
            return User::where('name','like',"%{$this->search}%")
                ->orderBy('name','asc')
                ->limit(5)->get();
        else
            return User::orderBy('name','asc')
                ->limit(5)->get();
    }

    public function updatedUserId()
    {
        $this->search = '';
        $this->dispatchBrowserEvent('close-modal-user');
    }
}

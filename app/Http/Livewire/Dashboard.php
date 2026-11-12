<?php

namespace App\Http\Livewire;

use App\Models\DetalleFactura;
use App\Models\Factura;
use App\Models\User;
use DateTime;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Spatie\Permission\Models\Role;
use App\Models\PaymentMethod;
use App\Models\Juicio;
use App\Models\Actividad;
use App\Models\Audiencia;
use App\Models\FinanzasJuicio;
use App\Models\PagosJuicio;
use Carbon\Carbon;

class Dashboard extends Component
{

    public $year, $salesByMonth_Data = [], $top5Data =[], $weekSales_Data=[], $listYears=[], $salesByPaymentMethod_Data = [];
    // Estadísticas de juicios (sección propia, todos ven todo)
    public $kpiJuicios = 0, $kpiAudiencias7 = 0, $kpiFirmasPendientes = 0, $kpiPorCobrar = 0;
    public $firmaPendientes = [];
    public $juiciosByMonth_Data = [], $juiciosByMateria_Data = [], $juiciosByEstado_Data = [], $audiencias14_Data = [], $audiencias14_Labels = [];
    public $tenantid;
    public $modalOpen = false;

    // Estado inicial del sistema
    public $esSaas = false;
    public $planNombre = 'Licencia local';
    public $planEstado = 'Activo';
    public $planMonto = 0;
    public $planVencimiento = null;
    public $tenantDominio = null;
    public $configItems = [];
    public $configCompletos = 0;
    public $configTotal = 0;
    public $configPorcentaje = 0;
    public $configCompleta = false;

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

        $this->getJuiciosKpis();
        $this->getJuiciosByMonth();
        $this->getJuiciosByMateria();
        $this->getJuiciosByEstado();
        $this->getAudiencias14();
        $this->prepararDiagnostico();

        return view('livewire.dash.component')->layout('layouts.theme.app');
    }

    public function getTenantId()
    {
        $this->tenantid = auth()->user()->tenant_id;
        
    }

    private function menusProcesales(): array
    {
        $menus = [
            ['label' => 'provincias', 'route' => 'provincias', 'exists' => \App\Models\Provincia::exists()],
            ['label' => 'cantones', 'route' => 'cantones', 'exists' => \App\Models\Canton::exists()],
            ['label' => 'unidades judiciales', 'route' => 'unidades', 'exists' => \App\Models\Unidad::exists()],
            ['label' => 'materias', 'route' => 'materias', 'exists' => \App\Models\Materia::exists()],
            ['label' => 'procedimientos', 'route' => 'procedimientos', 'exists' => \App\Models\Procedimiento::exists()],
            ['label' => 'asuntos', 'route' => 'asuntos', 'exists' => \App\Models\Asunto::exists()],
            ['label' => 'fases procesales', 'route' => 'fases-procesales', 'exists' => \App\Models\Fase::exists()],
            ['label' => 'estados procesales', 'route' => 'estados-procesales', 'exists' => \App\Models\EstadoProcesal::exists()],
            ['label' => 'especialidades', 'route' => 'especialidades', 'exists' => \App\Models\Especialidad::exists()],
            ['label' => 'tipos de actividades', 'route' => 'tipoactividad', 'exists' => \App\Models\TipoActividad::exists()],
            ['label' => 'plantillas de actividades', 'route' => 'plantillas-tipos-actividad', 'exists' => \App\Models\PlantillaTipoActividad::exists()],
            ['label' => 'alertas de juicios', 'route' => 'prioridad-alertas', 'exists' => \App\Models\PrioridadAlerta::exists()],
        ];

        $items = [];

        foreach ($menus as $menu) {
            $items[] = [
                'label' => $menu['exists']
                    ? $menu['label']
                    : 'Configurar ' . $menu['label'],
                'description' => $menu['exists']
                    ? 'Menú configurado.'
                    : 'Aún no hay registros en este menú.',
                'done' => (bool) $menu['exists'],
                'url' => route($menu['route']),
                'action' => 'Configurar',
            ];
        }

        return $items;
    }

    private function prepararDiagnostico(): void
    {
        $this->esSaas = function_exists('tenant') && tenant();
        $tenant = $this->esSaas ? tenant() : null;
        $empresa = app(\App\Services\EmpresaConfiguracionService::class)->empresa();
        $settings = $empresa['settings'];
        $mail = \App\Models\MailConfiguration::first();
        $firmaPersonal = User::whereNotNull('firma_path')->exists();

        if ($this->esSaas && $tenant) {
            $this->planNombre = ucwords(str_replace(['_', '-'], ' ', $tenant->suscription_type ?: 'Demo'));
            $this->planEstado = (int) ($tenant->status ?? 0) === 1 ? 'Activo' : 'Pendiente';
            $this->planMonto = (float) ($tenant->amount ?? 0);
            $this->planVencimiento = $tenant->bill_date
                ? Carbon::parse($tenant->bill_date)->format('d/m/Y')
                : null;
            $this->tenantDominio = optional($tenant->domains->first())->domain;
        } else {
            $this->planNombre = 'Licencia local';
            $this->planEstado = 'Activo';
            $this->planMonto = 0;
            $this->planVencimiento = null;
            $this->tenantDominio = null;
        }

        $empresaOk = $empresa['ok'];

        $certificadoFacturacion = app(\App\Services\CertificadoFacturacionService::class)->estado();
        $firmaFacturaOk = $certificadoFacturacion['ok'];

        $correoOk = $mail
            && filled($mail->host)
            && filled($mail->username)
            && filled($mail->from_address);

        $firmaDespachoOk = ($settings && filled($settings->firma_actividades_cert_file))
            || ($settings && $settings->firma_actividades_mismo_certificado && filled($settings->cert_file))
            || $firmaPersonal;

        $items = [];

        $items[] = [
            'label' => 'Datos de la empresa',
            'description' => 'Razón social, RUC, dirección y correo.',
            'done' => $empresaOk,
            'url' => route('settings'),
            'action' => 'Configurar',
        ];

        $items[] = [
            'label' => 'Firma electrónica para facturar',
            'description' => $certificadoFacturacion['ok']
                ? 'Certificado listo para emitir comprobantes.'
                : $certificadoFacturacion['message'],
            'done' => $firmaFacturaOk,
            'url' => route('settings'),
            'action' => 'Configurar',
        ];
          $items[] = [
            'label' => 'Firma del despacho',
            'description' => 'Certificado institucional o firma personal de un abogado.',
            'done' => $firmaDespachoOk,
            'url' => route('settings'),
            'action' => 'Configurar',
        ];

        if (!$this->esSaas) {
            $items[] = [
                'label' => 'Correo de facturación',
                'description' => 'SMTP desde el que se enviará el PDF de las facturas.',
                'done' => $correoOk,
                'url' => route('mail.settings'),
                'action' => 'Configurar',
            ];
        }

        $materiasSinUnidad = \App\Models\Materia::whereDoesntHave('unidades')->count();
        $itemUnidadesMaterias = [
            'label' => 'Asignar unidades a materias',
            'description' => $materiasSinUnidad > 0
                ? $materiasSinUnidad . ' materia(s) sin unidad judicial. Asígnalas desde Sitios > Unidades para que aparezcan en carátula.'
                : 'Todas las materias tienen al menos una unidad judicial.',
            'done' => $materiasSinUnidad === 0,
            'url' => route('unidades'),
            'action' => 'Ir a Unidades',
        ];

        $itemsProcesales = $this->menusProcesales();
        $indiceMaterias = collect($itemsProcesales)->search(fn ($item) => $item['url'] === route('materias'));

        if ($indiceMaterias === false) {
            $itemsProcesales[] = $itemUnidadesMaterias;
        } else {
            array_splice($itemsProcesales, $indiceMaterias + 1, 0, [$itemUnidadesMaterias]);
        }

        $items = array_merge($items, $itemsProcesales);

        $this->configTotal = count($items);
        $this->configCompletos = collect($items)->where('done', true)->count();
        $this->configItems = collect($items)->where('done', false)->values()->all();
        $this->configPorcentaje = $this->configTotal > 0
            ? (int) round($this->configCompletos * 100 / $this->configTotal)
            : 0;
        $this->configCompleta = $this->configCompletos === $this->configTotal;
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

    // ── JUICIOS: KPIs ──
    public function getJuiciosKpis()
    {
        $this->kpiJuicios = Juicio::count();
        $this->kpiAudiencias7 = Audiencia::where('estado', 'Programada')
            ->whereBetween('fecha_hora', [Carbon::now(), Carbon::now()->copy()->addDays(7)])
            ->count();
        $this->kpiFirmasPendientes = Actividad::where('estado_firma', 'pendiente')
            ->whereHas('tipoActividad', fn ($q) => $q->where('es_firmable', true))
            ->count();

        $this->firmaPendientes = Actividad::with(['juicio', 'tipoActividad'])
            ->where('estado_firma', 'pendiente')
            ->whereHas('tipoActividad', fn ($q) => $q->where('es_firmable', true))
            ->get()
            ->groupBy('juicio_id')
            ->map(function ($actividades) {
                $primera = $actividades->first();
                $codigo = $primera->juicio->cod_satje ?? ('#' . $primera->juicio_id);
                $tipos = $actividades->pluck('tipoActividad.nombre')
                    ->filter()
                    ->unique()
                    ->take(3)
                    ->implode(', ');

                return [
                    'juicio_id' => $primera->juicio_id,
                    'codigo' => $codigo,
                    'total' => $actividades->count(),
                    'tipos' => $tipos,
                    'url' => '/juicios?juicio=' . $primera->juicio_id,
                ];
            })
            ->values()
            ->all();

        $pactado = (float) FinanzasJuicio::selectRaw('COALESCE(SUM(honorarios_totales),0) + COALESCE(SUM(gastos_extras),0) as t')->value('t');
        $cobrado = (float) PagosJuicio::where('estado', 'Aprobado')->sum('monto');
        $this->kpiPorCobrar = max(0, $pactado - $cobrado);
    }

    // ── JUICIOS: por mes (año seleccionado, por fecha_inicio) ──
    public function getJuiciosByMonth()
    {
        $rows = Juicio::selectRaw('MONTH(fecha_inicio) as m, COUNT(*) as total')
            ->whereYear('fecha_inicio', $this->year)
            ->groupBy('m')
            ->pluck('total', 'm')
            ->toArray();
        $this->juiciosByMonth_Data = [];
        for ($m = 1; $m <= 12; $m++) {
            array_push($this->juiciosByMonth_Data, (int) ($rows[$m] ?? 0));
        }
    }

    // ── JUICIOS: top 5 por materia ──
    public function getJuiciosByMateria()
    {
        $this->juiciosByMateria_Data = Juicio::join('asuntos as a', 'juicios.asunto_id', '=', 'a.id')
            ->join('procedimientos as p', 'a.procedimiento_id', '=', 'p.id')
            ->join('materias as m', 'p.materia_id', '=', 'm.id')
            ->select(DB::raw('m.nombre as materia, COUNT(juicios.id) as total'))
            ->whereYear('juicios.fecha_inicio', $this->year)
            ->groupBy('m.nombre')
            ->orderByDesc(DB::raw('COUNT(juicios.id)'))
            ->limit(5)
            ->get()->toArray();
        $faltan = 5 - count($this->juiciosByMateria_Data);
        for ($i = 0; $i < $faltan; $i++) {
            array_push($this->juiciosByMateria_Data, ['materia' => '-', 'total' => 0]);
        }
    }

    // ── JUICIOS: por estado procesal ──
    public function getJuiciosByEstado()
    {
        $this->juiciosByEstado_Data = Juicio::join('estados_procesales as e', 'juicios.estado_procesal_id', '=', 'e.id')
            ->select(DB::raw('e.nombre as estado, COUNT(juicios.id) as total'))
            ->whereYear('juicios.fecha_inicio', $this->year)
            ->groupBy('e.nombre')
            ->orderByDesc(DB::raw('COUNT(juicios.id)'))
            ->get()->toArray();
    }

    // ── AUDIENCIAS: próximos 14 días por día ──
    public function getAudiencias14()
    {
        $this->audiencias14_Data = [];
        $this->audiencias14_Labels = [];
        for ($d = 0; $d < 14; $d++) {
            $fecha = Carbon::today()->copy()->addDays($d);
            $this->audiencias14_Labels[] = $fecha->format('d/m');
            array_push($this->audiencias14_Data, (int) Audiencia::whereDate('fecha_hora', $fecha->toDateString())->count());
        }
    }

    // ecuchar cuadno la propiedad year se actualice
    public function updatedYear()
    {
        $this->dispatchBrowserEvent('reload-scripts');
    }
}

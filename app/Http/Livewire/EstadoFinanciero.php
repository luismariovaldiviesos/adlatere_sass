<?php

namespace App\Http\Livewire;

use Livewire\Component;
use App\Models\FinanzasJuicio;
use App\Models\PagosJuicio;
use App\Models\Consulta;
use App\Models\Factura;
use Illuminate\Support\Facades\DB;

// Panel central de finanzas (SOLO LECTURA): une juicios, consultas y
// facturación por FK sin duplicar montos en ninguna tabla.
class EstadoFinanciero extends Component
{
    public $resumen = [];
    public $topDeudores = [];
    public $consultasPendientes = [];
    public $cobrosNoFacturados = [];
    public $consultasPorFacturar = [];
    public $facturasRecientes = [];
    public $cortesiasCount = 0;

    public function mount()
    {
        $this->cargar();
    }

    private function cargar()
    {
        // 1. POR COBRAR
        $pactado = (float) FinanzasJuicio::selectRaw('COALESCE(SUM(honorarios_totales),0)+COALESCE(SUM(gastos_extras),0) as t')->value('t');
        $cobrado = (float) PagosJuicio::where('estado', 'Aprobado')->sum('monto');
        $pendConsultas = (float) Consulta::where('estado_pago', 'pendiente')->sum('costo');

        // 2. COBRADO NO FACTURADO
        $cobNoFacPagos = (float) PagosJuicio::where('estado', 'Aprobado')->whereNull('factura_id')->sum('monto');
        $cobNoFacCons = (float) Consulta::whereIn('estado_pago', ['pagada', 'en_facturacion'])->sum('costo');

        // 3. FACTURADO AUTORIZADO (neto de notas de crédito)
        $facturado = (float) Factura::whereNotNull('numeroAutorizacion')
            ->selectRaw("SUM(CASE WHEN codDoc = '04' THEN -total ELSE total END) as t")->value('t');

        // 4. CORTESÍAS
        $cortesias = Consulta::where('estado_pago', 'no_factura')->count();

        $this->resumen = [
            'porCobrar' => $pactado - $cobrado + $pendConsultas,
            'cobradoNoFacturado' => $cobNoFacPagos + $cobNoFacCons,
            'facturado' => $facturado,
            'cortesias' => $cortesias,
        ];

        $this->topDeudores = FinanzasJuicio::with('juicio')
            ->selectRaw("finanzas_juicios.*, (honorarios_totales+gastos_extras-COALESCE((SELECT SUM(monto) FROM pagos_juicios WHERE finanzas_juicios_id=finanzas_juicios.id AND estado='Aprobado'),0)) as saldo")
            ->having('saldo', '>', 0)
            ->orderByDesc('saldo')
            ->limit(10)->get()->toArray();

        $this->consultasPendientes = Consulta::with('customer:id,businame')
            ->where('estado_pago', 'pendiente')
            ->orderBy('costo', 'desc')->limit(10)
            ->get(['id', 'customer_id', 'costo', 'created_at'])->toArray();

        $this->cobrosNoFacturados = PagosJuicio::with(['finanza.juicio:id,cod_satje', 'cliente:id,businame'])
            ->where('estado', 'Aprobado')->whereNull('factura_id')
            ->orderBy('fecha_pago', 'desc')->limit(10)
            ->get(['id', 'finanzas_juicios_id', 'customer_id', 'monto', 'fecha_pago', 'metodo_pago'])->toArray();

        $this->consultasPorFacturar = Consulta::with('customer:id,businame')
            ->whereIn('estado_pago', ['pagada', 'en_facturacion'])
            ->orderBy('costo', 'desc')->limit(10)
            ->get(['id', 'customer_id', 'costo', 'estado_pago', 'factura_id'])->toArray();

        $this->facturasRecientes = Factura::with('customer:id,businame')
            ->whereNotNull('numeroAutorizacion')
            ->orderBy('fechaAutorizacion', 'desc')->limit(10)
            ->get(['id', 'secuencial', 'codDoc', 'customer_id', 'total', 'fechaAutorizacion'])->toArray();

        $this->cortesiasCount = $cortesias;
    }

    public function render()
    {
        return view('livewire.estado-financiero.component')->layout('layouts.theme.app');
    }
}

<?php

namespace App\Http\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use App\Models\Juicio;
use App\Models\FinanzasJuicio;
use App\Models\PagosJuicio;
use App\Models\JuicioHistorialEstado;
use Illuminate\Support\Facades\DB;

class FinanzasJuicios extends Component
{
    use WithPagination, WithFileUploads;

    public $search = '';
    public $filtro_estado = 'todos'; // 'todos', 'con_saldo', 'al_dia'
    private $pagination = 10;
    protected $paginationTheme = 'tailwind';

    // Juicio en gestión
    public $selected_juicio_id = null;
    public $juicioSeleccionado = null;
    public $showModalGestion = false;

    // Configuración de honorarios del juicio seleccionado
    public $fin_honorarios = 0;
    public $fin_gastos = 0;
    public $fin_notas_acuerdo = '';

    // Registro de nuevo abono
    public $pago_customer_id = '';
    public $pago_monto = '';
    public $pago_fecha = '';
    public $pago_metodo = 'Transferencia';
    public $pago_referencia = '';
    public $pago_notas = '';
    public $pago_comprobante = null;

    protected $listeners = [
        'cerrarGestionModal' => 'cerrarGestion'
    ];

    public function mount()
    {
        $this->pago_fecha = date('Y-m-d');
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingFiltroEstado()
    {
        $this->resetPage();
    }

    public function render()
    {
        // 1. Métricas Globales (KPIs)
        $totalPactado = (float) FinanzasJuicio::selectRaw('COALESCE(SUM(honorarios_totales),0) + COALESCE(SUM(gastos_extras),0) as total')->value('total');
        $totalRecaudado = (float) PagosJuicio::where('estado', 'Aprobado')->sum('monto');
        $totalSaldoPendiente = max(0, $totalPactado - $totalRecaudado);

        // 2. Consulta de Juicios con Finanzas
        $query = Juicio::with([
            'asunto.procedimiento.materia',
            'finanza.pagos',
            'actores',
            'demandados',
            'abogados'
        ]);

        if (trim($this->search) !== '') {
            $term = trim($this->search);
            $query->where(function($q) use ($term) {
                $q->where('cod_satje', 'like', "%{$term}%")
                  ->orWhereHas('asunto', fn($sub) => $sub->where('nombre', 'like', "%{$term}%"))
                  ->orWhereHas('actores', fn($sub) => $sub->where('businame', 'like', "%{$term}%"))
                  ->orWhereHas('demandados', fn($sub) => $sub->where('businame', 'like', "%{$term}%"));
            });
        }

        $juicios = $query->orderBy('id', 'desc')->paginate($this->pagination);

        if ($this->selected_juicio_id && $this->showModalGestion) {
            $this->juicioSeleccionado = Juicio::with([
                'asunto.procedimiento.materia',
                'finanza.pagos.cliente',
                'actores',
                'demandados',
                'abogados'
            ])->find($this->selected_juicio_id);
        }

        return view('livewire.finanzas-juicios', [
            'juicios'             => $juicios,
            'totalPactado'        => $totalPactado,
            'totalRecaudado'      => $totalRecaudado,
            'totalSaldoPendiente' => $totalSaldoPendiente,
            'showModalGestion'    => $this->showModalGestion,
            'juicioSeleccionado'  => $this->juicioSeleccionado,
        ])->layout('layouts.theme.app');
    }

    public function abrirGestion($juicioId)
    {
        $this->selected_juicio_id = $juicioId;
        $this->juicioSeleccionado = Juicio::with([
            'asunto.procedimiento.materia',
            'finanza.pagos.cliente',
            'actores',
            'demandados',
            'abogados'
        ])->findOrFail($juicioId);

        // Asegurar que exista el registro de finanzas
        $finanza = FinanzasJuicio::firstOrCreate(
            ['juicio_id' => $juicioId],
            [
                'honorarios_totales' => 0,
                'gastos_extras'      => 0,
                'notas_acuerdo'      => null
            ]
        );

        $this->fin_honorarios = $finanza->honorarios_totales;
        $this->fin_gastos = $finanza->gastos_extras;
        $this->fin_notas_acuerdo = $finanza->notas_acuerdo;

        $this->resetAbonoForm();
        $this->showModalGestion = true;
        $this->dispatchBrowserEvent('open-modal-gestion');
    }

    public function cerrarGestion()
    {
        $this->showModalGestion = false;
        $this->selected_juicio_id = null;
        $this->juicioSeleccionado = null;
        $this->resetAbonoForm();
        $this->dispatchBrowserEvent('close-modal-gestion');
    }

    public function resetAbonoForm()
    {
        $this->pago_customer_id = '';
        $this->pago_monto = '';
        $this->pago_fecha = date('Y-m-d');
        $this->pago_metodo = 'Transferencia';
        $this->pago_referencia = '';
        $this->pago_notas = '';
        $this->pago_comprobante = null;
        $this->resetErrorBag();
    }

    public function saveFinanzas()
    {
        if (!auth()->user()->can('menu_finanzas_juicios') && !auth()->user()->hasRole('Admin') && !auth()->user()->hasRole('Asistente')) {
            $this->noty('No tiene permisos para modificar costos.', 'noty', false, 'error');
            return;
        }

        $this->validate([
            'fin_honorarios' => 'required|numeric|min:0',
            'fin_gastos'     => 'required|numeric|min:0',
        ]);

        $finanzas = FinanzasJuicio::updateOrCreate(
            ['juicio_id' => $this->selected_juicio_id],
            [
                'honorarios_totales' => $this->fin_honorarios,
                'gastos_extras'      => $this->fin_gastos,
                'notas_acuerdo'      => $this->fin_notas_acuerdo
            ]
        );

        JuicioHistorialEstado::create([
            'juicio_id'          => $this->selected_juicio_id,
            'user_id'            => auth()->id(),
            'estado_procesal_id' => $this->juicioSeleccionado->estado_procesal_id ?? null,
            'tipo_movimiento'    => 'finanzas_actualizadas',
            'referencia_tipo'    => 'FinanzasJuicio',
            'referencia_id'      => $finanzas->id,
            'descripcion'        => 'Se actualizaron los honorarios y gastos del caso desde Cobros de Juicios.',
        ]);

        $this->noty('Costos del juicio actualizados correctamente.', 'noty', false, 'success');
        $this->recargarJuicioSeleccionado();
    }

    public function savePago()
    {
        if (!auth()->user()->can('menu_finanzas_juicios') && !auth()->user()->hasRole('Admin') && !auth()->user()->hasRole('Asistente')) {
            $this->noty('No tiene permisos para registrar cobros.', 'noty', false, 'error');
            return;
        }

        $this->validate([
            'pago_customer_id' => 'required',
            'pago_monto'       => 'required|numeric|min:0.01',
            'pago_fecha'       => 'required|date',
            'pago_metodo'      => 'required',
            'pago_comprobante' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:4096'
        ], [
            'pago_customer_id.required' => 'Seleccione el cliente que realiza el pago.',
            'pago_monto.required'       => 'Ingrese un monto válido.',
            'pago_monto.min'            => 'El monto debe ser mayor a 0.',
            'pago_fecha.required'       => 'La fecha de pago es requerida.',
        ]);

        $finanza = FinanzasJuicio::where('juicio_id', $this->selected_juicio_id)->first();
        if (!$finanza) {
            $this->noty('Primero configure los honorarios del juicio.', 'noty', false, 'warning');
            return;
        }

        $rutaArchivo = null;
        if ($this->pago_comprobante) {
            $rutaArchivo = $this->pago_comprobante->store('comprobantes_pagos', 'public');
        }

        $pago = PagosJuicio::create([
            'finanzas_juicios_id'    => $finanza->id,
            'customer_id'            => $this->pago_customer_id,
            'user_id'                => auth()->id(),
            'monto'                  => $this->pago_monto,
            'fecha_pago'             => $this->pago_fecha,
            'metodo_pago'            => $this->pago_metodo,
            'referencia_transaccion' => $this->pago_referencia,
            'notas'                  => $this->pago_notas,
            'comprobante_ruta'       => $rutaArchivo,
            'estado'                 => 'Aprobado'
        ]);

        JuicioHistorialEstado::create([
            'juicio_id'          => $this->selected_juicio_id,
            'user_id'            => auth()->id(),
            'estado_procesal_id' => $this->juicioSeleccionado->estado_procesal_id ?? null,
            'tipo_movimiento'    => 'pago_registrado',
            'referencia_tipo'    => 'PagosJuicio',
            'referencia_id'      => $pago->id,
            'descripcion'        => "Se registró un abono de $ " . number_format($this->pago_monto, 2) . " por {$this->pago_metodo}.",
        ]);

        $this->resetAbonoForm();
        $this->noty('Abono registrado exitosamente.', 'noty', false, 'success');
        $this->recargarJuicioSeleccionado();
    }

    public function destroyPago($pagoId)
    {
        if (!auth()->user()->can('menu_finanzas_juicios') && !auth()->user()->hasRole('Admin') && !auth()->user()->hasRole('Asistente')) {
            $this->noty('No tiene permisos para anular pagos.', 'noty', false, 'error');
            return;
        }

        $pago = PagosJuicio::find($pagoId);
        if (!$pago) {
            $this->noty('El pago no existe.', 'noty', false, 'error');
            return;
        }
        if ($pago->factura_id) {
            $this->noty('No se puede anular un pago ya facturado. Anule la factura primero.', 'noty', false, 'warning');
            return;
        }

        $montoAnulado = $pago->monto;
        $pago->update(['estado' => 'Anulado']);

        JuicioHistorialEstado::create([
            'juicio_id'          => $this->selected_juicio_id,
            'user_id'            => auth()->id(),
            'estado_procesal_id' => $this->juicioSeleccionado->estado_procesal_id ?? null,
            'tipo_movimiento'    => 'pago_anulado',
            'referencia_tipo'    => 'PagosJuicio',
            'referencia_id'      => $pago->id,
            'descripcion'        => "Se anuló el abono de $ " . number_format($montoAnulado, 2) . " (registro conservado para auditoría).",
        ]);

        $this->noty('El pago ha sido marcado como ANULADO.', 'noty', false, 'success');
        $this->recargarJuicioSeleccionado();
    }

    private function recargarJuicioSeleccionado()
    {
        if ($this->selected_juicio_id) {
            $this->juicioSeleccionado = Juicio::with([
                'asunto.procedimiento.materia',
                'finanza.pagos.cliente',
                'actores',
                'demandados',
                'abogados'
            ])->find($this->selected_juicio_id);
        }
    }

    public function noty($msg, $eventName = 'noty', $reset = false, $action = "success")
    {
        $this->dispatchBrowserEvent($eventName, ['msg' => $msg, 'type' => $action, 'action' => $action]);
    }
}

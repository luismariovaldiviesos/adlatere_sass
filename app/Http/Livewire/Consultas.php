<?php

namespace App\Http\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use App\Models\Consulta;
use App\Models\Customer;
use App\Models\Asunto;
use App\Models\Materia;
use App\Models\Procedimiento;
use App\Models\Juicio;
use App\Models\Actividad;
use App\Models\TipoActividad;
use App\Models\EstadoProcesal;
use App\Models\Factura;
use App\Models\DetalleFactura;
use App\Models\Product;
use App\Models\Category;
use App\Models\Documento;
use Illuminate\Support\Facades\DB;

class Consultas extends Component
{
    use WithPagination, WithFileUploads;

    public $action = 'Listado', $componentName = 'Consultas', $search = '', $form = false, $selected_id = 0;
    private $pagination = 10;
    protected $paginationTheme = 'tailwind';

    // Formulario
    public $customer_id, $asunto_id, $abogado_id, $costo = 0, $notas;
    public $estado_pago = 'pendiente', $estado_atencion = 'pendiente';
    public $fecha_pago, $metodo_pago = 'Efectivo', $comprobante, $comprobante_actual, $facturar_ahora = false;

    // Cadena materia -> procedimiento -> asunto
    public $materias = [], $procedimientos = [], $asuntos = [];
    public $materia_id, $procedimiento_id;

    // Buscador de clientes (misma UX que Juicios)
    public $showDropdown = false;
    public $searchCustomer = '';
    public $customers = [];
    public $cliente_nombre = '';

    // Filtros del panel (abogado ve sus asignadas)
    public $filtroAtencion = 'todos'; // todos|pendiente|atendida|convertida
    public $filtroPago = 'todos';     // todos|pendiente|pagada|facturada|no_factura
    public $soloMias = true;
    public $esPanelAbogado = false; // true cuando se entra por "Mis consultas"

    public $abogados = [];

    public function mount()
    {
        $this->materias = Materia::orderBy('nombre', 'asc')->get();
        // Abogados = usuarios del sistema (todos los activos, incl. Admin:
        // hoy todos tus usuarios son Admin y el filtro anterior dejaba el combo vacío)
        $this->abogados = \App\Models\User::where('profile', 'abogado')->where('status', 'ACTIVE')
            ->orderBy('name', 'asc')->get();
        // Panel propio del abogado: solo sus asignadas, sin alta de consultas
        if (request()->route() && request()->route()->getName() === 'mis-consultas') {
            $this->esPanelAbogado = true;
            $this->soloMias = true;
        }
    }

    private function esAdmin()
    {
        $u = auth()->user();
        if (!$u) return false;
        if (isset($u->profile) && $u->profile === 'Admin') return true;
        if (method_exists($u, 'hasRole') && $u->hasRole('Admin')) return true;
        return false;
    }

    // Solo admin o el abogado asignado (o consulta sin asignar) pueden operarla
    private function puedeOperar(Consulta $consulta)
    {
        if ($this->esAdmin()) return true;
        if (!$consulta->abogado_id) return true;
        return (int) $consulta->abogado_id === (int) auth()->id();
    }

    // Asistente/admin: datos y cobros. Abogado: solo notas y atención.
    private function puedeAdministrar()
    {
        if ($this->esAdmin()) return true;
        return auth()->user()->can('menu_facturar');
    }

    // Editor visible solo para el abogado asignado (y admin). Nunca para recepción.
    private function puedeVerNotas()
    {
        if ($this->esAdmin()) return true;
        return $this->abogado_id && (int) $this->abogado_id === (int) auth()->id();
    }

    // Perfil abogado (por campo profile o rol Spatie). Atender, notas y
    // convertir exigen este perfil ADEMÁS de la asignación.
    private function esAbogado($user = null)
    {
        $u = $user ?: auth()->user();
        if (!$u) return false;
        if (isset($u->profile) && $u->profile === 'Abogado') return true;
        if (method_exists($u, 'hasRole') && $u->hasRole('Abogado')) return true;
        return false;
    }

    public function updatedSearchCustomer($value)
    {
        $this->customer_id = null;
        $this->showDropdown = true;
        if (strlen($value) > 0) {
            $this->customers = Customer::where('businame', 'like', "%{$value}%")
                ->orWhere('valueidenti', 'like', "%{$value}%")
                ->orderBy('businame', 'asc')->limit(5)->get();
        } else {
            $this->customers = [];
        }
    }

    public function selectCustomer($id, $name)
    {
        $this->customer_id = $id;
        $this->cliente_nombre = $name;
        $this->searchCustomer = $name;
        $this->customers = [];
        $this->showDropdown = false;
    }

    public function updatedMateriaId($value)
    {
        $this->procedimientos = Procedimiento::where('materia_id', $value)->orderBy('nombre', 'asc')->get();
        $this->procedimiento_id = null;
        $this->asunto_id = null;
        $this->asuntos = [];
    }

    public function updatedProcedimientoId($value)
    {
        $this->asuntos = Asunto::where('procedimiento_id', $value)->orderBy('nombre', 'asc')->get();
        $this->asunto_id = null;
    }

    // Autollenado según condición de pago elegida por recepción
    public function updatedEstadoPago($value)
    {
        if ($value === 'no_factura') {
            $this->costo = 0;
            $this->fecha_pago = null;
            $this->metodo_pago = 'No se cobra';
            $this->facturar_ahora = false;
        }
        if ($value === 'pendiente') {
            // Los datos del cobro se llenan cuando se pague (botón $ o edición)
            $this->fecha_pago = null;
            $this->metodo_pago = 'Efectivo';
            $this->facturar_ahora = false;
        }
        if ($value === 'pagada' && !$this->fecha_pago) {
            $this->fecha_pago = now()->toDateString();
        }
    }

    public function render()
    {
        if ($this->esPanelAbogado) {
            $this->soloMias = true; // el panel propio siempre filtra por asignadas
        }
        // Recepción (admin o menu_consultas) ve TODAS. Solo el abogado
        // puro (sin menu_consultas) queda acotado a sus asignadas.
        $veTodas = $this->esAdmin() || auth()->user()->can('menu_consultas');
        $q = Consulta::with(['customer', 'asunto.procedimiento.materia', 'abogado', 'juicio']);

        if (!$veTodas || ($this->esAdmin() && $this->soloMias)) {
            $q->where('abogado_id', auth()->id());
        }

        if ($this->filtroAtencion !== 'todos') {
            $q->where('estado_atencion', $this->filtroAtencion);
        }
        if ($this->filtroPago !== 'todos') {
            $q->where('estado_pago', $this->filtroPago);
        }

        if (strlen(trim($this->search)) > 0) {
            $s = trim($this->search);
            $q->where(function ($qq) use ($s) {
                $qq->whereHas('customer', function ($c) use ($s) {
                    $c->where('businame', 'like', "%{$s}%")
                      ->orWhere('valueidenti', 'like', "%{$s}%");
                })->orWhereHas('asunto', function ($a) use ($s) {
                    $a->where('nombre', 'like', "%{$s}%");
                });
            });
        }

        $info = $q->orderBy('id', 'desc')->paginate($this->pagination);

        $base = Consulta::query();
        if (!$veTodas || ($this->esAdmin() && $this->soloMias)) {
            $base->where('abogado_id', auth()->id());
        }
        if ($this->filtroAtencion !== 'todos') {
            $base->where('estado_atencion', $this->filtroAtencion);
        }
        // Cuadros de cuadre: facturadas vs no facturadas (conteo + valores)
        $totales = [
            'facturadas_n'     => (clone $base)->where('estado_pago', 'facturada')->count(),
            'facturadas_valor' => (clone $base)->where('estado_pago', 'facturada')->sum('costo'),
            'nofacturadas_n'     => (clone $base)->where('estado_pago', '!=', 'facturada')->count(),
            'nofacturadas_valor' => (clone $base)->where('estado_pago', '!=', 'facturada')->sum('costo'),
        ];

        $puedeEntrar = $this->esPanelAbogado
            ? auth()->user()->can('menu_mis_consultas')
            : auth()->user()->can('menu_consultas');

        return view('livewire.consultas.component', [
            'consultas' => $info,
            'puedeAdministrar' => $this->puedeAdministrar(),
            'puedeVerNotas' => $this->puedeVerNotas(),
            'puedeEntrar' => $puedeEntrar,
            'totales' => $totales,
        ])->layout('layouts.theme.app');
    }

    public function noty($msg, $eventName = 'noty', $reset = true, $action = "")
    {
        $this->dispatchBrowserEvent($eventName, ['msg' => $msg, 'type' => 'success', 'action' => $action]);
        if ($reset) $this->resetUI();
    }

    public function addNew()
    {
        if (!$this->puedeAdministrar()) return; // el abogado no crea consultas
        $this->resetForm();
        $this->fecha_pago = now()->toDateString();
        $this->form = true;
        $this->action = 'Agregar';
    }

    public function CloseModal()
    {
        $this->resetForm();
        $this->form = false;
        $this->noty(null, 'close-modal');
    }

    public function resetUI()
    {
        $this->resetPage();
        $this->resetValidation();
        $this->resetForm();
        $this->form = false;
    }

    private function resetForm()
    {
        $this->resetValidation();
        $this->reset([
            'customer_id', 'asunto_id', 'abogado_id', 'costo', 'notas',
            'estado_pago', 'estado_atencion', 'selected_id',
            'materia_id', 'procedimiento_id', 'searchCustomer',
            'cliente_nombre', 'customers', 'showDropdown',
            'fecha_pago', 'metodo_pago', 'comprobante', 'comprobante_actual', 'facturar_ahora',
        ]);
        $this->costo = 0;
        $this->estado_pago = 'pendiente';
        $this->estado_atencion = 'pendiente';
        $this->metodo_pago = 'Efectivo';
        $this->facturar_ahora = false;
        $this->procedimientos = [];
        $this->asuntos = [];
        $this->action = 'Listado';
    }

    // Asistente: crea la consulta CON su condición de pago (el abogado no interviene aquí)
    public function Store()
    {
        if (!$this->puedeAdministrar()) return;
        if (!$this->selected_id && auth()->user()->cannot('crear_consulta')) return;
        if ($this->selected_id && auth()->user()->cannot('editar_datos_consulta')) return;
        $anterior = $this->selected_id ? Consulta::find($this->selected_id) : null;

        $this->validate(array_merge(Consulta::rules(), [
            'comprobante' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
        ]), Consulta::$messages);

        // Si no es efectivo, el comprobante es obligatorio (transferencia, tarjeta u otro)
        if ($this->estado_pago === 'pagada' && $this->metodo_pago !== 'Efectivo'
            && !$this->comprobante && !($anterior->comprobante_ruta ?? null)) {
            $this->addError('comprobante', 'Adjunte el comprobante de la transferencia/pago.');
            return;
        }

        $ruta = null;
        if ($this->comprobante) {
            $ruta = $this->comprobante->store('documentos_juicios', 'public');
        }

        $consulta = Consulta::updateOrCreate(['id' => $this->selected_id], [
            'customer_id' => $this->customer_id,
            'asunto_id'   => $this->asunto_id,
            'abogado_id'  => $this->abogado_id ?: null,
            'costo'       => $this->estado_pago === 'no_factura' ? 0 : $this->costo,
            'notas'       => $this->notas,
            'estado_pago' => $this->estado_pago,
            'fecha_pago'  => $this->estado_pago === 'pagada' ? ($this->fecha_pago ?: now()->toDateString()) : null,
            'metodo_pago' => $this->estado_pago === 'pagada' ? $this->metodo_pago : ($this->estado_pago === 'no_factura' ? 'No se cobra' : null),
            'comprobante_ruta' => $ruta ?: ($anterior->comprobante_ruta ?? null),
        ]);

        // Al editar también se persiste el estado de atención (si lo cambió aquí).
        // Nunca se puede marcar 'convertida' desde este formulario.
        if ($this->selected_id && in_array($this->estado_atencion, ['pendiente', 'atendida'])) {
            $consulta->estado_atencion = $this->estado_atencion;
            $consulta->save();
        }

        $msg = $this->selected_id > 0 ? 'Consulta actualizada' : 'Consulta registrada';

        // Si al crear marcó pagada + facturar ahora, avisa que facturación viene después
        if (!$this->selected_id && $consulta->estado_pago === 'pagada' && $this->facturar_ahora) {
            $this->selected_id = $consulta->id;
            $this->facturarPendiente();
        }

        $this->resetForm();
        $this->form = false;
        $this->dispatchBrowserEvent('noty', ['msg' => $msg, 'type' => 'success', 'action' => '']);
    }

    // Facturación automática: pendiente de implementar (muestra aviso por ahora)
    public function facturarPendiente()
    {
        $this->dispatchBrowserEvent('noty', ['msg' => 'Pendiente: el método de facturación automática está en construcción.', 'type' => 'success', 'action' => '']);
    }

    // Descarga del comprobante de pago (solo recepción; el abogado no ve cobros)
    public function descargarComprobante($id)
    {
        if (!$this->puedeAdministrar()) return;
        $consulta = Consulta::find($id ?: $this->selected_id);
        if (!$consulta || !$consulta->comprobante_ruta) {
            $this->dispatchBrowserEvent('noty', ['msg' => 'Esta consulta no tiene comprobante cargado.', 'type' => 'error', 'action' => '']);
            return;
        }
        if (!\Storage::disk('public')->exists($consulta->comprobante_ruta)) {
            $this->dispatchBrowserEvent('noty', ['msg' => 'El archivo ya no existe en el servidor.', 'type' => 'error', 'action' => '']);
            return;
        }
        $ext = strtolower(pathinfo($consulta->comprobante_ruta, PATHINFO_EXTENSION));
        return \Storage::disk('public')->download($consulta->comprobante_ruta, 'comprobante-consulta-' . $consulta->id . '.' . $ext);
    }

    // Abogado asignado: solo guarda notas y estado de atención. Nunca toca pagos ni datos.
    public function guardarNotas()
    {
        $consulta = Consulta::find($this->selected_id);
        if (!$consulta) {
            $this->dispatchBrowserEvent('noty', ['msg' => 'La consulta ya no existe. Recargue la página.', 'type' => 'error', 'action' => '']);
            return;
        }
        if (!$this->esAbogado()) {
            $this->dispatchBrowserEvent('noty', ['msg' => 'Solo un usuario con perfil de abogado puede guardar notas.', 'type' => 'error', 'action' => '']);
            return;
        }
        if (!$this->esAdmin() && (int) $consulta->abogado_id !== (int) auth()->id()) {
            $this->dispatchBrowserEvent('noty', ['msg' => 'Solo el abogado asignado puede guardar notas.', 'type' => 'error', 'action' => '']);
            return;
        }
        if (auth()->user()->cannot('guardar_notas_consulta')) {
            $this->dispatchBrowserEvent('noty', ['msg' => 'Su perfil no tiene permiso para guardar notas.', 'type' => 'error', 'action' => '']);
            return;
        }
        $this->validate(['notas' => 'nullable|string']);
        $consulta->notas = $this->notas;
        if (in_array($this->estado_atencion, ['pendiente', 'atendida'])) {
            $consulta->estado_atencion = $this->estado_atencion;
        }
        $consulta->save();
        $this->dispatchBrowserEvent('set-consulta-editor-content', ['content' => $this->notas ?? '']);
        $this->dispatchBrowserEvent('noty', ['msg' => 'Notas y estado de atención guardados.', 'type' => 'success', 'action' => '']);
    }

    public function Edit($id)
    {
        $consulta = Consulta::with(['customer', 'asunto.procedimiento'])->find($id);
        if (!$consulta) return;
        if (!$this->puedeOperar($consulta)) {
            $this->dispatchBrowserEvent('noty', ['msg' => 'No tiene permiso para ver esta consulta.', 'type' => 'success', 'action' => '']);
            return;
        }

        $this->selected_id = $consulta->id;
        $this->customer_id = $consulta->customer_id;
        $this->cliente_nombre = $consulta->customer->businame ?? '';
        $this->searchCustomer = $this->cliente_nombre;
        $this->asunto_id = $consulta->asunto_id;
        $this->abogado_id = $consulta->abogado_id;
        $this->costo = $consulta->costo;
        $this->notas = $consulta->notas;
        $this->estado_pago = $consulta->estado_pago;
        $this->estado_atencion = $consulta->estado_atencion;
        $this->fecha_pago = $consulta->fecha_pago ? $consulta->fecha_pago->format('Y-m-d') : null;
        $this->metodo_pago = $consulta->metodo_pago ?: 'Efectivo';
        $this->comprobante_actual = $consulta->comprobante_ruta;

        // Cadena jerárquica para que el asunto quede seleccionado
        $this->procedimiento_id = $consulta->asunto->procedimiento_id ?? null;
        $this->materia_id = $consulta->asunto->procedimiento->materia_id ?? null;
        if ($this->materia_id) {
            $this->procedimientos = Procedimiento::where('materia_id', $this->materia_id)->orderBy('nombre', 'asc')->get();
        }
        if ($this->procedimiento_id) {
            $this->asuntos = Asunto::where('procedimiento_id', $this->procedimiento_id)->orderBy('nombre', 'asc')->get();
        }

        $this->action = 'Editar';
        $this->form = true;
        $this->dispatchBrowserEvent('set-consulta-editor-content', ['content' => $this->notas ?? '']);
    }

    // Abogado atiende la consulta
    public function atender($id)
    {
        $consulta = Consulta::find($id);
        if (!$consulta) return;
        if (!$this->puedeOperar($consulta)) {
            $this->dispatchBrowserEvent('noty', ['msg' => 'No tiene permiso para atender esta consulta.', 'type' => 'success', 'action' => '']);
            return;
        }
        if (!$this->esAbogado()) {
            $this->dispatchBrowserEvent('noty', ['msg' => 'Solo un usuario con perfil de abogado puede atender consultas.', 'type' => 'error', 'action' => '']);
            return;
        }
        if (auth()->user()->cannot('atender_consulta')) {
            $this->dispatchBrowserEvent('noty', ['msg' => 'Su perfil no tiene permiso para atender consultas.', 'type' => 'error', 'action' => '']);
            return;
        }
        if ($consulta->estado_atencion === 'convertida') {
            $this->dispatchBrowserEvent('noty', ['msg' => 'La consulta ya fue convertida en juicio.', 'type' => 'success', 'action' => '']);
            return;
        }
        // Si no tenía abogado, se autoasigna quien atiende
        if (!$consulta->abogado_id) {
            $consulta->abogado_id = auth()->id();
        }
        $consulta->estado_atencion = 'atendida';
        $consulta->fecha_atencion = now();
        $consulta->save();

        $this->dispatchBrowserEvent('noty', ['msg' => 'Consulta marcada como atendida.', 'type' => 'success', 'action' => '']);
    }

    // Correcciones de caja (solo asistente)
    public function marcarNoFactura($id)
    {
        $consulta = Consulta::find($id);
        if (!$consulta || !$this->puedeAdministrar()) return;
        if (auth()->user()->cannot('marcar_pagada_consulta')) return;
        if ($consulta->estado_pago === 'facturada' || $consulta->factura_id) return;
        $consulta->estado_pago = 'no_factura';
        $consulta->save();
        $this->dispatchBrowserEvent('noty', ['msg' => 'Consulta marcada como no se cobra.', 'type' => 'success', 'action' => '']);
    }

    // Corrección de caja (solo asistente): pendiente -> pagada
    public function marcarPagada($id)
    {
        $consulta = Consulta::find($id);
        if (!$consulta || !$this->puedeAdministrar()) return;
        if (auth()->user()->cannot('marcar_pagada_consulta')) return;
        if ($consulta->estado_pago === 'facturada' || $consulta->factura_id) return;
        $consulta->estado_pago = 'pagada';
        $consulta->fecha_pago = $consulta->fecha_pago ?: now()->toDateString();
        $consulta->metodo_pago = $consulta->metodo_pago ?: 'Efectivo';
        $consulta->save();
        $this->dispatchBrowserEvent('noty', ['msg' => 'Consulta marcada como pagada.', 'type' => 'success', 'action' => '']);
    }

    // Envío automático a facturación: crea borrador (sin autorizar SRI) + detalle y lo vincula
    public function enviarFacturacion($id)
    {
        $consulta = Consulta::with('customer', 'asunto')->find($id);
        if (!$consulta) return;
        if (!$this->puedeAdministrar()) {
            $this->dispatchBrowserEvent('noty', ['msg' => 'No tiene permiso para facturar. Solo recepción.', 'type' => 'success', 'action' => '']);
            return;
        }
        if (auth()->user()->cannot('facturar_consulta')) return;
        if ($consulta->estado_pago === 'facturada' || $consulta->factura_id) {
            $this->dispatchBrowserEvent('noty', ['msg' => 'La consulta ya fue facturada.', 'type' => 'success', 'action' => '']);
            return;
        }
        if ((float) $consulta->costo <= 0) {
            $this->dispatchBrowserEvent('noty', ['msg' => 'Defina un costo mayor a 0 antes de facturar.', 'type' => 'success', 'action' => '']);
            return;
        }
        // Tope SRI consumidor final
        if (($consulta->customer->valueidenti ?? '') === '9999999999999' && (float) $consulta->costo > 50) {
            $this->dispatchBrowserEvent('noty', ['msg' => 'Consumidor final no puede superar $50. Registre los datos del cliente.', 'type' => 'success', 'action' => '']);
            return;
        }

        DB::beginTransaction();
        try {
            $categoria = Category::first();
            $producto = Product::firstOrCreate(
                ['code' => 'SERV-CONSULTA'],
                [
                    'name' => 'Servicio de consulta legal',
                    'price' => (float) $consulta->costo,
                    'category_id' => $categoria ? $categoria->id : 1,
                    'es_servicio' => 1,
                    'stock' => 0,
                ]
            );

            $tmp = new Factura();
            $factura = Factura::create([
                'secuencial'    => $tmp->secuencial('01'),
                'codDoc'        => '01',
                'claveAcceso'   => (new Factura())->claveAcceso('01'),
                'customer_id'   => $consulta->customer_id,
                'user_id'       => auth()->id(),
                'subtotal'      => (float) $consulta->costo,
                'descuento'     => 0,
                'total'         => (float) $consulta->costo,
                'formaPago'     => '01',
            ]);

            DetalleFactura::create([
                'factura_id'     => $factura->id,
                'product_id'     => $producto->id,
                'cantidad'       => 1,
                'descripcion'    => 'Consulta legal #' . $consulta->id . ' - ' . ($consulta->asunto->nombre ?? '') . ' - ' . ($consulta->customer->businame ?? ''),
                'precioUnitario' => (float) $consulta->costo,
                'descuento'      => 0,
                'total'          => (float) $consulta->costo,
            ]);

            $consulta->factura_id = $factura->id;
            $consulta->estado_pago = 'facturada';
            $consulta->save();

            DB::commit();
            $this->dispatchBrowserEvent('noty', ['msg' => 'Borrador de factura #' . $factura->id . ' creado. Revíselo y emítalo en Facturación.', 'type' => 'success', 'action' => '']);
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error enviando consulta a facturación: ' . $e->getMessage());
            $this->dispatchBrowserEvent('noty', ['msg' => 'Error al facturar: ' . $e->getMessage(), 'type' => 'success', 'action' => '']);
        }
    }

    // Conversión a juicio: crea carátula (asunto + actor + abogado) y vincula juicio_id
    public function convertirAJuicio($id)
    {
        $consulta = Consulta::with(['customer', 'asunto'])->find($id);
        if (!$consulta) return;
        if (!$this->esAbogado()) {
            $this->dispatchBrowserEvent('noty', ['msg' => 'Solo un usuario con perfil de abogado puede convertir en juicio.', 'type' => 'error', 'action' => '']);
            return;
        }
        if (!$this->esAdmin() && (int) $consulta->abogado_id !== (int) auth()->id()) {
            $this->dispatchBrowserEvent('noty', ['msg' => 'Solo el abogado asignado o el admin pueden convertirla en juicio.', 'type' => 'success', 'action' => '']);
            return;
        }
        if (auth()->user()->cannot('convertir_consulta')) return;
        if ($consulta->estado_atencion === 'convertida' || $consulta->juicio_id) {
            $this->dispatchBrowserEvent('noty', ['msg' => 'La consulta ya fue convertida en juicio.', 'type' => 'success', 'action' => '']);
            return;
        }

        DB::beginTransaction();
        try {
            $estadoInicial = EstadoProcesal::orderBy('id', 'asc')->first();
            if (!$estadoInicial) {
                throw new \Exception('No hay estados procesales registrados.');
            }

            // Código temporal único (la carátula exige cod_satje único al editar)
            $juicio = Juicio::create([
                'cod_satje'          => 'CONS-' . $consulta->id . '-' . now()->format('Ymd'),
                'asunto_id'          => $consulta->asunto_id,
                'estado_procesal_id' => $estadoInicial->id,
                'fecha_inicio'       => now()->toDateString(),
                'prioridad'          => 'Media',
            ]);

            // Cliente de la consulta -> actor de la carátula, marcado como cliente del despacho
            if (!$juicio->participantes()->where('customer_id', $consulta->customer_id)->exists()) {
                $juicio->participantes()->attach($consulta->customer_id, ['rol' => 'actor', 'es_cliente' => true]);
            } else {
                $juicio->participantes()->updateExistingPivot($consulta->customer_id, ['es_cliente' => true]);
            }

                 // Abogado de la consulta -> patrocinador de la carátula (nunca en cero)
            $idAbogado = $consulta->abogado_id ?: auth()->id();
            $consulta->abogado_id = $consulta->abogado_id ?: auth()->id();
            $juicio->abogados()->attach($idAbogado, ['rol_en_juicio' => 'Abogado Patrocinador']);

            // Comprobante de la consulta -> gestor documental del juicio nuevo
            if ($consulta->comprobante_ruta) {
                Documento::create([
                    'juicio_id'     => $juicio->id,
                    'origen_tipo'   => 'Consulta',
                    'origen_id'     => $consulta->id,
                    'nombre'        => 'Comprobante consulta #' . $consulta->id,
                    'ruta_archivo'  => $consulta->comprobante_ruta,
                    'tipo_archivo'  => strtolower(pathinfo($consulta->comprobante_ruta, PATHINFO_EXTENSION)),
                    'tamaño_archivo' => null,
                ]);
            }

            // Notas de la consulta -> primera actividad del juicio
            if (trim(strip_tags($consulta->notas ?? '')) !== '') {
                $tipo = TipoActividad::orderBy('id', 'asc')->first();
                if ($tipo) {
                    Actividad::create([
                        'juicio_id'         => $juicio->id,
                        'tipo_actividad_id' => $tipo->id,
                        'user_id'           => $consulta->abogado_id ?: auth()->id(),
                        'origen'            => 'Interno',
                        'fecha_actividad'   => now(),
                        'descripcion'       => 'Nota inicial de la consulta #' . $consulta->id,
                        'contenido'         => $consulta->notas,
                        'estado_firma'      => 'no_requerida',
                    ]);
                }
            }

            \App\Models\JuicioHistorialEstado::create([
                'juicio_id'          => $juicio->id,
                'user_id'            => auth()->id(),
                'estado_procesal_id' => $juicio->estado_procesal_id,
                'tipo_movimiento'    => 'juicio_creado',
                'referencia_tipo'    => 'Consulta',
                'referencia_id'      => $consulta->id,
                'descripcion'        => 'Juicio creado desde la consulta #' . $consulta->id,
            ]);

            $consulta->estado_atencion = 'convertida';
            $consulta->fecha_atencion = $consulta->fecha_atencion ?: now();
            $consulta->juicio_id = $juicio->id;
            $consulta->save();

            DB::commit();
            $this->dispatchBrowserEvent('noty', ['msg' => 'Juicio ' . $juicio->cod_satje . ' creado. Asunto, cliente y abogado cargados en carátula.', 'type' => 'success', 'action' => '']);
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error convirtiendo consulta a juicio: ' . $e->getMessage());
            $this->dispatchBrowserEvent('noty', ['msg' => 'Error al convertir: ' . $e->getMessage(), 'type' => 'success', 'action' => '']);
        }
    }

    public function destroy($id)
    {
        $consulta = Consulta::find($id);
        if (!$consulta || auth()->user()->cannot('eliminar_consulta')) return;
        if ($consulta->juicio_id || $consulta->factura_id) {
            $this->dispatchBrowserEvent('noty', ['msg' => 'No se puede eliminar: ya tiene juicio o factura vinculada.', 'type' => 'success', 'action' => '']);
            return;
        }
        $consulta->delete();
        $this->dispatchBrowserEvent('noty', ['msg' => 'Consulta eliminada.', 'type' => 'success', 'action' => '']);
    }
}

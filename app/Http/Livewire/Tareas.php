<?php

namespace App\Http\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use App\Models\Tarea;
use App\Models\TareaHistorial;
use App\Models\Juicio;
use App\Models\Actividad;
use App\Models\TipoActividad;
use Illuminate\Support\Facades\DB;

class Tareas extends Component
{
    use WithPagination, WithFileUploads;

    public $action = 'Listado', $componentName = 'Tareas', $search = '', $form = false, $selected_id = 0;
    private $pagination = 10;
    protected $paginationTheme = 'tailwind';

    // Formulario
    public $titulo, $descripcion, $tipo = 'externa';
    public $juicio_id, $searchJuicio = '', $juicios = [], $juicio_nombre = '', $showJuicioDropdown = false;
    public $asignado_a, $fecha_limite;
    public $abogados = [];
    public $tiposActividades = [];

    // Hoja privada del asignado: redacción + adjuntos (solo asignado y admin)
    public $contenido, $adjunto, $adjuntos = [];

    // Filtros (admin y recepción ven todas por defecto; el abogado siempre las suyas)
    public $filtroEstado = 'todas'; // abiertas|todas|pendiente|en_proceso|completada|convertida|cancelada|vencidas
    public $soloMias = false;

    // Conversión a actividad
    public $convertir_id = null, $conv_tipo_actividad_id;

    // Ver detalles
    public $verDetalle_id = null;
    public $tareaDetalle = null;
    public $verActividad_id = null;
    public $actividadDetalle = null;

    // Cambiar fecha de vencimiento (solo Admin/Asistente)
    public $fecha_id = null, $fecha_edit = null, $abrirFecha = false;

    public function mount()
    {
        $this->abogados = \App\Models\User::where('status', 'ACTIVE')
            ->whereIn('profile', ['Abogado', 'Pasante'])
            ->orderBy('name', 'asc')->get();
        $this->tiposActividades = TipoActividad::orderBy('nombre', 'asc')->get();
    }

    private function esAdmin()
    {
        $u = auth()->user();
        if (!$u) return false;
        if (isset($u->profile) && $u->profile === 'Admin') return true;
        if (method_exists($u, 'hasRole') && $u->hasRole('Admin')) return true;
        return false;
    }

    private function esAsistente()
    {
        $u = auth()->user();
        if (!$u) return false;
        if (isset($u->profile) && $u->profile === 'Asistente') return true;
        if (method_exists($u, 'hasRole') && $u->hasRole('Asistente')) return true;
        return false;
    }

    private function puedeEliminar()
    {
        return $this->esAdmin() || $this->esAsistente();
    }

    // Fecha de vencimiento: solo Admin y Asistente, y solo en tareas no finalizadas
    private function puedeEditarFecha()
    {
        return $this->esAdmin() || $this->esAsistente();
    }

    // Datos y asignación: admin o recepción. El resto solo ejecuta lo suyo.
    private function puedeGestionar()
    {
        if ($this->esAdmin()) return true;
        return auth()->user()->can('crear_tarea');
    }

    // Hoja privada (redacción + adjuntos): solo el asignado (admin y asistente solo miran la tabla)
    private function puedeVerPrivado()
    {
        if (!$this->selected_id) return false;
        return (int) $this->asignado_a === (int) auth()->id();
    }

    private function tareaSeleccionadaVencida()
    {
        if (!$this->selected_id) return false;
        $t = Tarea::find($this->selected_id);
        return $t ? (bool) $t->esta_vencida : false;
    }

    // Estados finales: la tarea ya no admite cambios de contenido ni adjuntos
    private function esFinal($estado)
    {
        return in_array($estado, ['completada', 'convertida', 'cancelada'], true);
    }

    private function tieneAvance(Tarea $t)
    {
        return trim(strip_tags($t->contenido ?? '')) !== '' || $t->adjuntos()->exists();
    }

    private function registrarHistorial($tareaId, $tipo, $descripcion, $datosAnteriores = null, $datosNuevos = null)
    {
        TareaHistorial::registrar($tareaId, $tipo, $descripcion, $datosAnteriores, $datosNuevos);
    }

    public function updatedSearchJuicio($value)
    {
        $this->juicio_id = null;
        $this->showJuicioDropdown = true;
        if (strlen($value) > 0) {
            $this->juicios = Juicio::with('asunto:id,nombre')
                ->where('cod_satje', 'like', "%{$value}%")
                ->orWhereHas('asunto', function ($q) use ($value) {
                    $q->where('nombre', 'like', "%{$value}%");
                })
                ->orderBy('id', 'desc')->limit(5)->get();
        } else {
            $this->juicios = [];
        }
    }

    public function selectJuicio($id, $cod)
    {
        $this->juicio_id = $id;
        $this->juicio_nombre = $cod;
        $this->searchJuicio = $cod;
        $this->juicios = [];
        $this->showJuicioDropdown = false;
    }

    public function updatedTipo($value)
    {
        if ($value !== 'juicio') {
            $this->juicio_id = null;
            $this->searchJuicio = '';
            $this->juicio_nombre = '';
            $this->juicios = [];
        }
    }

    public function render()
    {
        $veTodas = $this->esAdmin() || auth()->user()->can('crear_tarea');

        $q = Tarea::with(['asignado:id,name', 'juicio:id,cod_satje'])->withCount('adjuntos');

        if (!$veTodas || $this->soloMias) {
            $q->where('asignado_a', auth()->id());
        }

        if ($this->filtroEstado === 'abiertas') {
            $q->whereIn('estado', ['pendiente', 'en_proceso']);
        } elseif ($this->filtroEstado === 'vencidas') {
            $q->whereIn('estado', ['pendiente', 'en_proceso'])
              ->whereDate('fecha_limite', '<', now()->toDateString());
        } elseif ($this->filtroEstado !== 'todas') {
            $q->where('estado', $this->filtroEstado);
        }

        if (strlen(trim($this->search)) > 0) {
            $s = trim($this->search);
            $q->where(function ($qq) use ($s) {
                $qq->where('titulo', 'like', "%{$s}%")
                   ->orWhere('descripcion', 'like', "%{$s}%");
            });
        }

        $info = $q->orderBy('id', 'desc')->paginate($this->pagination);

        return view('livewire.tareas.component', [
            'tareas' => $info,
            'puedeGestionar' => $this->puedeGestionar(),
            'puedeVerPrivado' => $this->puedeVerPrivado(),
            'tareaVencida' => $this->tareaSeleccionadaVencida(),
            'puedeEntrar' => auth()->user()->can('menu_tareas'),
            'puedeEliminar' => $this->puedeEliminar(),
            'puedeEditarFecha' => $this->puedeEditarFecha(),
        ])->layout('layouts.theme.app');
    }

    public function noty($msg, $eventName = 'noty', $reset = true, $action = "")
    {
        $this->dispatchBrowserEvent($eventName, ['msg' => $msg, 'type' => 'success', 'action' => $action]);
        if ($reset) $this->resetUI();
    }

    public function addNew()
    {
        if (!$this->puedeGestionar()) return;
        $this->resetForm();
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
            'titulo', 'descripcion', 'contenido', 'tipo', 'juicio_id', 'searchJuicio', 'juicios',
            'juicio_nombre', 'showJuicioDropdown', 'asignado_a', 'fecha_limite',
            'selected_id', 'convertir_id', 'conv_tipo_actividad_id', 'adjunto', 'adjuntos',
            'fecha_id', 'fecha_edit', 'abrirFecha',
        ]);
        $this->tipo = 'externa';
        $this->action = 'Listado';
    }

    public function Store()
    {
        if (!$this->puedeGestionar()) return;
        if (!$this->selected_id && auth()->user()->cannot('crear_tarea')) return;

        $this->validate([
            'titulo' => 'required|string|max:255',
            'descripcion' => 'nullable|string',
            'tipo' => 'required|in:externa,juicio',
            'juicio_id' => 'required_if:tipo,juicio|nullable|exists:juicios,id',
            'asignado_a' => 'required|exists:users,id',
            'fecha_limite' => 'nullable|date|after_or_equal:today',
        ], [
            'titulo.required' => 'Indique el título de la tarea.',
            'juicio_id.required_if' => 'Seleccione el juicio vinculado.',
            'asignado_a.required' => 'Seleccione a quién se asigna.',
            'fecha_limite.after_or_equal' => 'La fecha límite no puede ser anterior a hoy.',
        ]);

        // El asignado debe ser abogado o pasante
        $resp = \App\Models\User::find($this->asignado_a);
        if (!$resp || !in_array($resp->profile, ['Abogado', 'Pasante'])) {
            $this->addError('asignado_a', 'Solo se puede asignar a abogados o pasantes.');
            return;
        }

        $esNueva = !$this->selected_id;

        if (!$esNueva) {
            $actual = Tarea::find($this->selected_id);
            if (!$actual || $actual->estado !== 'en_proceso') {
                $this->dispatchBrowserEvent('noty', ['msg' => 'Solo se puede editar una tarea iniciada (en proceso).', 'type' => 'error', 'action' => '']);
                return;
            }
            if ((int) $actual->asignado_a !== (int) auth()->id()) {
                $this->dispatchBrowserEvent('noty', ['msg' => 'Solo el asignado puede editar la tarea. Para ampliar la fecha límite use la opción correspondiente.', 'type' => 'error', 'action' => '']);
                return;
            }
            if ($actual->esta_vencida) {
                $this->dispatchBrowserEvent('noty', ['msg' => 'La tarea está vencida; no se puede editar. Solicite a Admin/Asistente ampliar la fecha límite.', 'type' => 'error', 'action' => '']);
                return;
            }
        }

        $datos = [
            'titulo' => $this->titulo,
            'descripcion' => $this->descripcion,
            'tipo' => $this->tipo,
            'juicio_id' => $this->tipo === 'juicio' ? $this->juicio_id : null,
            'asignado_a' => $this->asignado_a,
            'creado_por' => $this->selected_id ? Tarea::find($this->selected_id)->creado_por : auth()->id(),
            'fecha_limite' => $this->fecha_limite ?: null,
        ];
        // La redacción solo la pisa quien puede ver la hoja privada (si no, se conserva)
        if ($this->puedeVerPrivado() || !$this->selected_id) {
            $datos['contenido'] = $this->contenido;
        }

        if ($esNueva) {
            $tarea = Tarea::create($datos);
            $this->registrarHistorial($tarea->id, 'creada', 'Tarea creada', null, $datos);
        } else {
            $tarea = Tarea::find($this->selected_id);
            $anteriores = $tarea->toArray();
            $tarea->update($datos);
            $this->registrarHistorial($tarea->id, 'editada', 'Tarea editada', $anteriores, $tarea->fresh()->toArray());
        }

        $msg = $this->selected_id > 0 ? 'Tarea actualizada' : 'Tarea asignada';
        $this->resetForm();
        $this->form = false;
        $this->dispatchBrowserEvent('noty', ['msg' => $msg, 'type' => 'success', 'action' => '']);
    }

    public function Edit($id)
    {
        $t = Tarea::find($id);
        if (!$t) return;
        // Solo el asignado abre la tarea (admin y asistente solo miran la tabla)
        if ((int) $t->asignado_a !== (int) auth()->id()) return;

        if ($t->esta_vencida) {
            $this->dispatchBrowserEvent('noty', ['msg' => 'La tarea está vencida; no se puede editar. Solicite a Admin/Asistente ampliar la fecha límite.', 'type' => 'error', 'action' => '']);
            return;
        }

        // Solo se puede editar si la tarea fue iniciada
        if ($t->estado !== 'en_proceso') {
            $this->dispatchBrowserEvent('noty', ['msg' => 'Debe pulsar "Iniciar" antes de editar la tarea.', 'type' => 'error', 'action' => '']);
            return;
        }

        $this->selected_id = $t->id;
        $this->titulo = $t->titulo;
        $this->descripcion = $t->descripcion;
        $this->contenido = $t->contenido;
        $this->adjuntos = $t->adjuntos()->orderBy('id', 'desc')->get()->toArray();
        $this->tipo = $t->tipo;
        $this->juicio_id = $t->juicio_id;
        $this->juicio_nombre = $t->juicio->cod_satje ?? '';
        $this->searchJuicio = $this->juicio_nombre;
        $this->asignado_a = $t->asignado_a;
        $this->fecha_limite = $t->fecha_limite ? $t->fecha_limite->format('Y-m-d') : null;
        $this->action = 'Editar';
        $this->form = true;
        $this->dispatchBrowserEvent('set-tarea-editor-content', ['content' => $this->contenido ?? '']);
    }

    // Avanzar o retroceder estado (quien ejecuta o gestión)
    public function cambiarEstado($id, $nuevo)
    {
        $t = Tarea::find($id);
        if (!$t) return;
        // Solo el asignado avanza estados
        if ((int) $t->asignado_a !== (int) auth()->id()) {
            $this->dispatchBrowserEvent('noty', ['msg' => 'Solo el asignado puede cambiar el estado.', 'type' => 'error', 'action' => '']);
            return;
        }
        if ($nuevo === 'completada' && $t->esta_vencida) {
            $this->dispatchBrowserEvent('noty', ['msg' => 'La tarea está vencida; no se puede completar. Solicite a Admin/Asistente ampliar la fecha límite.', 'type' => 'error', 'action' => '']);
            return;
        }

        $permitidas = [
            'pendiente' => ['en_proceso', 'cancelada'],
            'en_proceso' => ['completada', 'pendiente', 'cancelada'],
            'completada' => [],
            'convertida' => [],
            'cancelada' => ['pendiente'],
        ];
        if (!in_array($nuevo, $permitidas[$t->estado] ?? [])) {
            $this->dispatchBrowserEvent('noty', ['msg' => 'Transición no permitida.', 'type' => 'error', 'action' => '']);
            return;
        }
        if (in_array($nuevo, ['completada', 'en_proceso', 'pendiente', 'cancelada']) && auth()->user()->cannot('completar_tarea') && !$this->puedeGestionar()) return;

        // No se puede completar sin redacción/nota ni adjunto
        if ($nuevo === 'completada' && !$this->tieneAvance($t)) {
            $this->dispatchBrowserEvent('noty', ['msg' => 'Para completar la tarea debe escribir una nota/redacción o subir un archivo.', 'type' => 'error', 'action' => '']);
            return;
        }

        $anterior = $t->estado;
        $t->estado = $nuevo;
        $t->fecha_completada = $nuevo === 'completada' ? now() : null;
        $t->save();
        $this->registrarHistorial($t->id, 'estado_cambiado', "Estado cambiado de {$anterior} a {$nuevo}", ['estado' => $anterior], ['estado' => $nuevo]);
        $this->dispatchBrowserEvent('noty', ['msg' => 'Tarea: ' . $nuevo . '.', 'type' => 'success', 'action' => '']);
    }

    // Paso 1 convertir: abre el selector de tipo de actividad
    public function abrirConvertir($id)
    {
        $t = Tarea::with('juicio')->find($id);
        if (!$t) return;
        if (auth()->user()->cannot('convertir_tarea')) return;
        if ($t->tipo !== 'juicio' || !$t->juicio_id) return;
        if ($t->estado !== 'completada' || $t->actividad_id) return;
        if ((int) $t->asignado_a !== (int) auth()->id()) {
            $this->dispatchBrowserEvent('noty', ['msg' => 'Solo el asignado puede convertirla.', 'type' => 'error', 'action' => '']);
            return;
        }
        $this->convertir_id = $t->id;
        $this->conv_tipo_actividad_id = null;
    }

    public function cancelarConvertir()
    {
        $this->convertir_id = null;
        $this->conv_tipo_actividad_id = null;
    }

    // Ver detalles de tarea (todos los perfiles)
    public function verDetalles($id)
    {
        $this->verDetalle_id = $id;
        $this->tareaDetalle = \App\Models\Tarea::with([
            'asignado:id,name',
            'juicio:id,cod_satje',
            'adjuntos',
            'historiales.usuario:id,name'
        ])->find($id);
    }

    public function cerrarDetalles()
    {
        $this->verDetalle_id = null;
        $this->tareaDetalle = null;
    }

    // Ver detalles de actividad (todos los perfiles)
    public function verDetallesActividad($id)
    {
        $this->verActividad_id = $id;
        $this->actividadDetalle = \App\Models\Actividad::with([
            'tipoActividad',
            'juicio:id,cod_satje',
            'tarea:id,titulo',
            'user:id,name',
            'adjuntos'
        ])->find($id);
    }

    public function cerrarDetallesActividad()
    {
        $this->verActividad_id = null;
        $this->actividadDetalle = null;
    }

    // Cambiar fecha de vencimiento (Admin/Asistente, tareas no finalizadas)
    public function abrirFechaVencimiento($id)
    {
        if (!$this->puedeEditarFecha()) return;
        $t = Tarea::find($id);
        if (!$t) return;
        if (!in_array($t->estado, ['pendiente', 'en_proceso'], true)) {
            $this->dispatchBrowserEvent('noty', ['msg' => 'Solo se puede cambiar la fecha en tareas pendientes o en proceso.', 'type' => 'error', 'action' => '']);
            return;
        }
        $this->fecha_id = $t->id;
        $this->fecha_edit = $t->fecha_limite ? $t->fecha_limite->format('Y-m-d') : null;
        $this->abrirFecha = true;
    }

    public function cerrarFechaVencimiento()
    {
        $this->fecha_id = null;
        $this->fecha_edit = null;
        $this->abrirFecha = false;
        $this->resetValidation('fecha_edit');
    }

    public function guardarFechaVencimiento()
    {
        if (!$this->puedeEditarFecha()) return;
        $t = Tarea::find($this->fecha_id);
        if (!$t) {
            $this->cerrarFechaVencimiento();
            return;
        }
        if (!in_array($t->estado, ['pendiente', 'en_proceso'], true)) {
            $this->dispatchBrowserEvent('noty', ['msg' => 'Solo se puede cambiar la fecha en tareas pendientes o en proceso.', 'type' => 'error', 'action' => '']);
            $this->cerrarFechaVencimiento();
            return;
        }

        $this->validate(['fecha_edit' => 'nullable|date|after_or_equal:today'], [
            'fecha_edit.date' => 'Ingrese una fecha válida.',
            'fecha_edit.after_or_equal' => 'La fecha de vencimiento no puede ser anterior a hoy.',
        ]);

        $anterior = $t->fecha_limite ? $t->fecha_limite->format('Y-m-d') : null;
        $t->fecha_limite = $this->fecha_edit ?: null;
        $t->save();
        $nueva = $t->fecha_limite ? $t->fecha_limite->format('Y-m-d') : null;

        $this->registrarHistorial(
            $t->id,
            'fecha_cambiada',
            'Fecha de vencimiento cambiada de ' . ($anterior ? \Carbon\Carbon::parse($anterior)->format('d/m/Y') : 'sin fecha')
                . ' a ' . ($nueva ? $t->fecha_limite->format('d/m/Y') : 'sin fecha'),
            ['fecha_limite' => $anterior],
            ['fecha_limite' => $nueva]
        );

        $this->cerrarFechaVencimiento();
        $this->dispatchBrowserEvent('noty', ['msg' => 'Fecha de vencimiento actualizada.', 'type' => 'success', 'action' => '']);
    }

    // Paso 2 convertir: crea la actividad en el juicio (una sola vez)
    public function confirmarConvertir()
    {
        $t = Tarea::find($this->convertir_id);
        if (!$t) return;
        if (auth()->user()->cannot('convertir_tarea')) return;
        if ($t->tipo !== 'juicio' || !$t->juicio_id || $t->estado !== 'completada' || $t->actividad_id) return;
        if ((int) $t->asignado_a !== (int) auth()->id()) return;

        $this->validate(
            ['conv_tipo_actividad_id' => 'required|exists:tipos_actividades,id'],
            ['conv_tipo_actividad_id.required' => 'Seleccione el tipo de actividad.']
        );

        DB::beginTransaction();
        try {
            $tipo = TipoActividad::find($this->conv_tipo_actividad_id);
            $act = Actividad::create([
                'juicio_id' => $t->juicio_id,
                'tipo_actividad_id' => $tipo->id,
                'user_id' => auth()->id(),
                'origen' => 'Interno',
                'fecha_actividad' => now(),
                'descripcion' => 'Tarea #' . $t->id . ': ' . $t->titulo,
                'contenido' => $t->descripcion ?: $t->titulo,
                'estado_firma' => ($tipo && $tipo->es_firmable) ? 'pendiente' : 'no_requerida',
            ]);

            $t->actividad_id = $act->id;
            $t->estado = 'convertida';
            $t->save();
            $this->registrarHistorial($t->id, 'convertida', 'Tarea convertida en actividad del juicio', ['estado' => 'completada'], ['estado' => 'convertida', 'actividad_id' => $act->id]);

            \App\Models\JuicioHistorialEstado::create([
                'juicio_id' => $t->juicio_id,
                'user_id' => auth()->id(),
                'estado_procesal_id' => $t->juicio->estado_procesal_id ?? null,
                'tipo_movimiento' => 'tarea_convertida',
                'referencia_tipo' => 'Tarea',
                'referencia_id' => $t->id,
                'descripcion' => 'Tarea #' . $t->id . ' convertida en actividad: ' . $t->titulo,
            ]);

            DB::commit();
            $this->convertir_id = null;
            $this->conv_tipo_actividad_id = null;
            $this->dispatchBrowserEvent('noty', ['msg' => 'Tarea convertida en actividad del juicio.', 'type' => 'success', 'action' => '']);
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error convirtiendo tarea: ' . $e->getMessage());
            $this->dispatchBrowserEvent('noty', ['msg' => 'Error al convertir: ' . $e->getMessage(), 'type' => 'error', 'action' => '']);
        }
    }

    public function destroy($id)
    {
        $t = Tarea::find($id);
        if (!$t) return;
        if ($t->actividad_id) {
            $this->dispatchBrowserEvent('noty', ['msg' => 'No se puede eliminar: ya generó una actividad.', 'type' => 'error', 'action' => '']);
            return;
        }

        // No se puede eliminar si tiene acciones (contenido, adjuntos, estado cambiado)
        $tieneAcciones = trim(strip_tags($t->contenido ?? '')) !== ''
            || $t->adjuntos()->exists()
            || $t->historiales()->whereIn('tipo', ['estado_cambiado', 'contenido_guardado', 'adjunto_subido', 'adjunto_eliminado', 'convertida'])->exists();

        if ($tieneAcciones) {
            $this->dispatchBrowserEvent('noty', ['msg' => 'No se puede eliminar: la tarea tiene acciones registradas (redacción, adjuntos o cambios de estado).', 'type' => 'error', 'action' => '']);
            return;
        }

        // Solo Admin y Asistente pueden eliminar
        if (!$this->puedeEliminar()) {
            $this->dispatchBrowserEvent('noty', ['msg' => 'Solo el administrador o el asistente pueden eliminar tareas.', 'type' => 'error', 'action' => '']);
            return;
        }

        $this->registrarHistorial($t->id, 'eliminada', 'Tarea eliminada', $t->toArray(), null);
        $t->delete();
        $this->dispatchBrowserEvent('noty', ['msg' => 'Tarea eliminada.', 'type' => 'success', 'action' => '']);
    }

    // Hoja privada: guardar redacción (solo el asignado)
    public function guardarContenido()
    {
        $t = Tarea::find($this->selected_id);
        if (!$t) return;
        if ($this->esFinal($t->estado)) {
            $this->dispatchBrowserEvent('noty', ['msg' => 'La tarea ya está finalizada; no admite más cambios.', 'type' => 'error', 'action' => '']);
            return;
        }
        if ((int) $t->asignado_a !== (int) auth()->id()) {
            $this->dispatchBrowserEvent('noty', ['msg' => 'Solo el asignado puede guardar la redacción.', 'type' => 'error', 'action' => '']);
            return;
        }
        if (auth()->user()->cannot('completar_tarea')) {
            $this->dispatchBrowserEvent('noty', ['msg' => 'Sin permiso para guardar la redacción.', 'type' => 'error', 'action' => '']);
            return;
        }
        if ($t->esta_vencida) {
            $this->dispatchBrowserEvent('noty', ['msg' => 'La tarea está vencida; no se puede editar. Solicite a Admin/Asistente ampliar la fecha límite.', 'type' => 'error', 'action' => '']);
            return;
        }
        $this->validate(['contenido' => 'nullable|string']);
        $anterior = $t->contenido;
        $t->contenido = $this->contenido;
        $t->save();
        $this->registrarHistorial($t->id, 'contenido_guardado', 'Redacción guardada', ['contenido' => $anterior], ['contenido' => $this->contenido]);
        $this->dispatchBrowserEvent('set-tarea-editor-content', ['content' => $this->contenido ?? '']);
        $this->dispatchBrowserEvent('noty', ['msg' => 'Redacción guardada.', 'type' => 'success', 'action' => '']);
    }

    // Hoja privada: subir adjunto (solo el asignado)
    public function subirAdjunto()
    {
        $t = Tarea::find($this->selected_id);
        if (!$t) return;
        if ($this->esFinal($t->estado)) {
            $this->dispatchBrowserEvent('noty', ['msg' => 'La tarea ya está finalizada; no admite más adjuntos.', 'type' => 'error', 'action' => '']);
            return;
        }
        if ((int) $t->asignado_a !== (int) auth()->id()) {
            $this->dispatchBrowserEvent('noty', ['msg' => 'Solo el asignado puede subir adjuntos.', 'type' => 'error', 'action' => '']);
            return;
        }
        if (auth()->user()->cannot('completar_tarea')) {
            $this->dispatchBrowserEvent('noty', ['msg' => 'Sin permiso para subir adjuntos.', 'type' => 'error', 'action' => '']);
            return;
        }
        if ($t->esta_vencida) {
            $this->dispatchBrowserEvent('noty', ['msg' => 'La tarea está vencida; no se puede editar. Solicite a Admin/Asistente ampliar la fecha límite.', 'type' => 'error', 'action' => '']);
            return;
        }
        $this->validate(['adjunto' => 'required|file|mimes:pdf,doc,docx,jpg,jpeg,png|max:10240'], [
            'adjunto.required' => 'Seleccione un archivo.',
            'adjunto.mimes' => 'Solo PDF, Word o imágenes.',
            'adjunto.max' => 'Máximo 10 MB.',
        ]);
        $ruta = $this->adjunto->store('tarea_adjuntos', 'public');
        $adj = \App\Models\TareaAdjunto::create([
            'tarea_id' => $t->id,
            'user_id' => auth()->id(),
            'nombre' => $this->adjunto->getClientOriginalName(),
            'ruta_archivo' => $ruta,
            'tipo_archivo' => strtolower($this->adjunto->getClientOriginalExtension()),
            'tamaño_archivo' => round(filesize($this->adjunto->getRealPath()) / 1024, 2),
        ]);
        $this->registrarHistorial($t->id, 'adjunto_subido', 'Adjunto subido: ' . $adj->nombre, null, $adj->toArray());
        $this->adjunto = null;
        $this->adjuntos = $t->adjuntos()->orderBy('id', 'desc')->get()->toArray();
        $this->dispatchBrowserEvent('noty', ['msg' => 'Adjunto subido.', 'type' => 'success', 'action' => '']);
    }

    // Hoja privada: quitar adjunto (solo el asignado)
    public function quitarAdjunto($id)
    {
        $adj = \App\Models\TareaAdjunto::find($id);
        if (!$adj || (int) $adj->tarea_id !== (int) $this->selected_id) return;
        $t = Tarea::find($this->selected_id);
        if (!$t) return;
        if ($this->esFinal($t->estado)) {
            $this->dispatchBrowserEvent('noty', ['msg' => 'La tarea ya está finalizada; no admite cambios.', 'type' => 'error', 'action' => '']);
            return;
        }
        if ((int) $t->asignado_a !== (int) auth()->id()) return;
        if ($t->esta_vencida) {
            $this->dispatchBrowserEvent('noty', ['msg' => 'La tarea está vencida; no se puede editar. Solicite a Admin/Asistente ampliar la fecha límite.', 'type' => 'error', 'action' => '']);
            return;
        }
        if (\Storage::disk('public')->exists($adj->ruta_archivo)) {
            \Storage::disk('public')->delete($adj->ruta_archivo);
        }
        $nombreAdjunto = $adj->nombre;
        $adj->delete();
        $this->registrarHistorial($t->id, 'adjunto_eliminado', 'Adjunto eliminado: ' . $nombreAdjunto, $adj->toArray(), null);
        $this->adjuntos = $t->adjuntos()->orderBy('id', 'desc')->get()->toArray();
        $this->dispatchBrowserEvent('noty', ['msg' => 'Adjunto eliminado.', 'type' => 'success', 'action' => '']);
    }
}
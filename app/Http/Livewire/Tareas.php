<?php

namespace App\Http\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use App\Models\Tarea;
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
    public $filtroEstado = 'abiertas'; // abiertas|todas|pendiente|en_proceso|completada|convertida|cancelada|vencidas
    public $soloMias = false;

    // Conversión a actividad
    public $convertir_id = null, $conv_tipo_actividad_id;

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
            'puedeEntrar' => auth()->user()->can('menu_tareas'),
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
            'fecha_limite' => 'nullable|date',
        ], [
            'titulo.required' => 'Indique el título de la tarea.',
            'juicio_id.required_if' => 'Seleccione el juicio vinculado.',
            'asignado_a.required' => 'Seleccione a quién se asigna.',
        ]);

        // El asignado debe ser abogado o pasante
        $resp = \App\Models\User::find($this->asignado_a);
        if (!$resp || !in_array($resp->profile, ['Abogado', 'Pasante'])) {
            $this->addError('asignado_a', 'Solo se puede asignar a abogados o pasantes.');
            return;
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

        Tarea::updateOrCreate(['id' => $this->selected_id], $datos);

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

        $permitidas = [
            'pendiente' => ['en_proceso', 'cancelada'],
            'en_proceso' => ['completada', 'pendiente', 'cancelada'],
            'completada' => ['en_proceso'],
            'convertida' => [],
            'cancelada' => ['pendiente'],
        ];
        if (!in_array($nuevo, $permitidas[$t->estado] ?? [])) {
            $this->dispatchBrowserEvent('noty', ['msg' => 'Transición no permitida.', 'type' => 'error', 'action' => '']);
            return;
        }
        if (in_array($nuevo, ['completada', 'en_proceso', 'pendiente', 'cancelada']) && auth()->user()->cannot('completar_tarea') && !$this->puedeGestionar()) return;

        $t->estado = $nuevo;
        $t->fecha_completada = $nuevo === 'completada' ? now() : null;
        $t->save();
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
        // Con avances (redacción, adjuntos o estado movido) solo la elimina el asignado;
        // sin avances la puede eliminar cualquiera con acceso al módulo.
        $avance = trim(strip_tags($t->contenido ?? '')) !== ''
            || $t->adjuntos()->exists()
            || !in_array($t->estado, ['pendiente']);
        if ($avance && (int) $t->asignado_a !== (int) auth()->id()) {
            $this->dispatchBrowserEvent('noty', ['msg' => 'Solo el asignado puede eliminar una tarea con avances.', 'type' => 'error', 'action' => '']);
            return;
        }
        $t->delete();
        $this->dispatchBrowserEvent('noty', ['msg' => 'Tarea eliminada.', 'type' => 'success', 'action' => '']);
    }

    // Hoja privada: guardar redacción (solo el asignado)
    public function guardarContenido()
    {
        $t = Tarea::find($this->selected_id);
        if (!$t) return;
        if ((int) $t->asignado_a !== (int) auth()->id()) {
            $this->dispatchBrowserEvent('noty', ['msg' => 'Solo el asignado puede guardar la redacción.', 'type' => 'error', 'action' => '']);
            return;
        }
        if (auth()->user()->cannot('completar_tarea')) {
            $this->dispatchBrowserEvent('noty', ['msg' => 'Sin permiso para guardar la redacción.', 'type' => 'error', 'action' => '']);
            return;
        }
        $this->validate(['contenido' => 'nullable|string']);
        $t->contenido = $this->contenido;
        $t->save();
        $this->dispatchBrowserEvent('set-tarea-editor-content', ['content' => $this->contenido ?? '']);
        $this->dispatchBrowserEvent('noty', ['msg' => 'Redacción guardada.', 'type' => 'success', 'action' => '']);
    }

    // Hoja privada: subir adjunto (solo el asignado)
    public function subirAdjunto()
    {
        $t = Tarea::find($this->selected_id);
        if (!$t) return;
        if ((int) $t->asignado_a !== (int) auth()->id()) {
            $this->dispatchBrowserEvent('noty', ['msg' => 'Solo el asignado puede subir adjuntos.', 'type' => 'error', 'action' => '']);
            return;
        }
        if (auth()->user()->cannot('completar_tarea')) {
            $this->dispatchBrowserEvent('noty', ['msg' => 'Sin permiso para subir adjuntos.', 'type' => 'error', 'action' => '']);
            return;
        }
        $this->validate(['adjunto' => 'required|file|mimes:pdf,doc,docx,jpg,jpeg,png|max:10240'], [
            'adjunto.required' => 'Seleccione un archivo.',
            'adjunto.mimes' => 'Solo PDF, Word o imágenes.',
            'adjunto.max' => 'Máximo 10 MB.',
        ]);
        $ruta = $this->adjunto->store('tarea_adjuntos', 'public');
        \App\Models\TareaAdjunto::create([
            'tarea_id' => $t->id,
            'user_id' => auth()->id(),
            'nombre' => $this->adjunto->getClientOriginalName(),
            'ruta_archivo' => $ruta,
            'tipo_archivo' => strtolower($this->adjunto->getClientOriginalExtension()),
            'tamaño_archivo' => round(filesize($this->adjunto->getRealPath()) / 1024, 2),
        ]);
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
        if ((int) $t->asignado_a !== (int) auth()->id()) return;
        if (\Storage::disk('public')->exists($adj->ruta_archivo)) {
            \Storage::disk('public')->delete($adj->ruta_archivo);
        }
        $adj->delete();
        $this->adjuntos = $t->adjuntos()->orderBy('id', 'desc')->get()->toArray();
        $this->dispatchBrowserEvent('noty', ['msg' => 'Adjunto eliminado.', 'type' => 'success', 'action' => '']);
    }
}

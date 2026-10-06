<?php

namespace App\Http\Livewire;

use App\Models\User;
use Livewire\Component;
use Livewire\WithPagination;
use Spatie\Permission\Models\Role;
use App\Models\Especialidad;
use App\Models\Materia;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;

class Users extends Component
{
    use WithPagination, WithFileUploads;

    public $name='', $ci='', $phone ='', $email='', $profile ='cajero', $status = 'ACTIVE',  $password='',
    $temppass='', $selected_id ='', $search ='';
    public $componentName = 'Usuarios', $form  = false;

    public $action = 'Listado';
    protected $paginationTheme = 'tailwind';
    private $pagination = 10;
    public $especialidadesSeleccionadas = [];
    public $searchEspecialidad = '';

    public $firma_archivo;
    public $firma_password;
    public $tieneFirma = false;        // ← NUEVO: para mostrar estado en edición

        public $requiereReemplazo = false, $reemplazo_id, $casosAfectados = [];

    public function render()
    {
        if(strlen($this->search) > 0)
        {
            $users = User::where('name','like',"%{$this->search}%")
                           ->orWhere('email','like',"%{$this->search}%")
                           ->orderBy('id','asc')
                           ->paginate($this->pagination);
        }
        else
        {
            $users = User::orderBy('id','asc')
                           ->paginate($this->pagination);
        }
        return view('livewire.users.component',
        [
            'users' => $users,
            'roles' => Role::orderBy('name','asc')->get(),
            'especialidades' => Especialidad::join('materias as m', 'm.id', 'especialidades.materia_id')
            ->select('especialidades.*', 'm.nombre as materia')
            ->when($this->searchEspecialidad, function($q){
                $q->where('especialidades.nombre', 'like', "%{$this->searchEspecialidad}%")
                  ->orWhere('m.nombre', 'like', "%{$this->searchEspecialidad}%");
            })->orderBy('m.nombre','asc')->get(),
        ])
        ->layout('layouts.theme.app');
    }

    public function noty($msg, $eventName = 'noty', $reset = true, $action =""){
        $this->dispatchBrowserEvent($eventName, ['msg'=>$msg, 'type' => 'success', 'action' => $action ]);
        if($reset) $this->resetUI();
    }

    public function  addNew()
    {
        $this->resetUI();
        $this->form = true;
        $this->action = 'Agregar';
    }

    public  function  CloseModal()
    {
        $this->resetUI();
        $this->noty(null, 'close-modal');
    }

    public  function resetUI()
    {
        $this->resetValidation();
        $this->resetPage();
        $this->reset('name','ci','phone', 'status','selected_id','temppass','search','componentName', 
        'email','password','profile','form','especialidadesSeleccionadas','searchEspecialidad', 
        'firma_archivo', 'firma_password', 'tieneFirma', // ← AGREGADO tieneFirma
        'requiereReemplazo', 'reemplazo_id', 'casosAfectados' );  
        $this->profile = 'cajero';
        $this->status = 'ACTIVE';
    }

    public function Edit(User $user)
    {
        $this->selected_id = $user->id;
        $this->name = $user->name;
        $this->ci =  $user->ci;
        $this->phone =  $user->phone;
        $this->email = $user->email;
        $this->profile = $user->profile;
        $this->status = $user->status;
        $this->password = null;
        $this->temppass = $user->password;
        $this->especialidadesSeleccionadas = $user->especialidades->pluck('id')->toArray();
        
        // ← NUEVO: Cargar estado de firma actual
        $this->tieneFirma = !empty($user->firma_path);
        
        $this->form = true;
        $this->action = 'Editar';
    }

    // ← NUEVO: Validar certificado apenas se selecciona (feedback inmediato)
    public function updatedFirmaArchivo()
    {
        $this->validateOnly('firma_archivo', [
            'firma_archivo' => 'file|mimes:p12,pfx|max:2048',
        ], [
            'firma_archivo.file'  => 'No se pudo leer el archivo seleccionado.',
            'firma_archivo.mimes' => 'El archivo debe ser .p12 o .pfx válido.',
            'firma_archivo.max'   => 'El archivo no debe superar 2 MB.',
        ]);
    }

    public $listeners = ['resetUI','Destroy'];

    public function Store()
    {
        // 1. Validar datos de usuario
        $this->validate(User::rules($this->selected_id), User::$messages);

        // 2. Validar firma: ambos o ninguno, formato correcto
        $this->validate([
            'firma_archivo'  => 'nullable|file|mimes:p12,pfx|max:2048|required_with:firma_password',
            'firma_password' => 'nullable|string|min:1|required_with:firma_archivo',
        ], [
            'firma_archivo.required_with' => 'Escribió contraseña pero no seleccionó el archivo .p12.',
            'firma_password.required_with' => 'Seleccionó el archivo pero falta la contraseña de la firma.',
            'firma_archivo.mimes' => 'El archivo debe ser .p12 o .pfx.',
            'firma_archivo.max'   => 'El archivo no debe superar 2 MB.',
        ]);
               // B1. Si cambia perfil/rol o se bloquea, exigir reasignación previa
        $anterior = $this->selected_id ? \App\Models\User::find($this->selected_id) : null;
        $cambiaRol = $anterior && $anterior->profile !== $this->profile;
        $seBloquea = $anterior && $anterior->status !== 'LOCKED' && $this->status === 'LOCKED';
        if ($anterior && ($cambiaRol || $seBloquea)) {
            $juicios = \App\Models\Juicio::whereHas('abogados', function($q) use ($anterior) {
                $q->where('user_id', $anterior->id)
                  ->whereRaw("TRIM(LOWER(rol_en_juicio)) LIKE ?", ['%abogado%patrocinador%']);
            })->get(['id', 'cod_satje']);
            $nCons = \App\Models\Consulta::where('abogado_id', $anterior->id)
                ->whereIn('estado_atencion', ['pendiente', 'atendida'])->count();
            if ($juicios->count() > 0 || $nCons > 0) {
                $this->requiereReemplazo = true;
                $this->casosAfectados = $juicios->map(fn($j) => ['id' => $j->id, 'cod' => $j->cod_satje])->toArray();
                $this->dispatchBrowserEvent('noty', ['msg' => 'Tiene casos activos: seleccione reemplazante abajo y pulse Transferir casos.', 'type' => 'error', 'action' => '']);
                return;
            }
        }

        // 3. Crear/Actualizar usuario
        $user =  User::updateOrCreate(
            ['id' => $this->selected_id],
            [
                'name' =>  $this->name,
                'ci' => $this->ci,
                'phone' => $this->phone,
                'email' =>  $this->email,
                'profile' =>  $this->profile,
                'status' => $this->status,
                'password' => strlen($this->password) > 0 ? bcrypt($this->password) : $this->temppass,
                // Los campos de firma se guardan abajo condicionalmente
            ]
        );

        $user->syncRoles($this->profile);
        $user->especialidades()->sync($this->especialidadesSeleccionadas);

        // 4. Manejar certificado de firma (subida, reemplazo, conservación)
        if ($this->firma_archivo && $this->firma_password) {
            // Borrar certificado anterior si existe (evita huérfanos en disco)
            if ($user->firma_path && Storage::disk('local')->exists($user->firma_path)) {
                Storage::disk('local')->delete($user->firma_path);
            }

            // Guardar nuevo en disco PRIVADO ('local' = storage/app/... tenant-aware)
            $path = $this->firma_archivo->store('firmas_abogados', 'local');

            // Guardar en BD
            $user->firma_path = $path;
            $user->firma_password = Crypt::encryptString($this->firma_password);
            $user->save();

            $this->noty('Certificado de firma guardado correctamente', 'noty', false);
        } elseif ($this->tieneFirma && !$this->firma_archivo && !$this->firma_password) {
            // Usuario ya tenía certificado y NO subió uno nuevo → CONSERVAR el actual (no hacer nada)
        }

        $this->noty($this->selected_id > 0 ? 'Usuario actualizado' : 'Usuario registrado');
        $this->resetUI();
    }

        public function ejecutarReemplazo()
    {
        $this->validate(['reemplazo_id' => 'required|exists:users,id'], [
            'reemplazo_id.required' => 'Seleccione el abogado reemplazante.',
        ]);
        $viejo = \App\Models\User::find($this->selected_id);
        $nuevo = \App\Models\User::find($this->reemplazo_id);
        if (!$viejo || !$nuevo || (int) $viejo->id === (int) $nuevo->id) return;
        DB::transaction(function() use ($viejo, $nuevo) {
            $juicios = \App\Models\Juicio::whereHas('abogados', fn($q) => $q->where('user_id', $viejo->id))->get();
            foreach ($juicios as $j) {
                $pivote = $j->abogados()->where('user_id', $viejo->id)->first();
                $rol = $pivote ? $pivote->pivot->rol_en_juicio : 'Abogado Patrocinador';
                $desde = $pivote ? $pivote->pivot->created_at : $j->created_at;
                // 1. Adjuntar entrante PRIMERO (el juicio nunca queda en cero)
                if (!$j->abogados()->where('user_id', $nuevo->id)->exists()) {
                    $j->abogados()->attach($nuevo->id, ['rol_en_juicio' => $rol]);
                }
                // 2. Snapshot de la gestión del saliente
                $acts = \App\Models\Actividad::where('juicio_id', $j->id)->where('user_id', $viejo->id)->where('created_at', '>=', $desde)->count();
                $firmas = \App\Models\Actividad::where('juicio_id', $j->id)->where('firmado_por_user_id', $viejo->id)->count();
                $pagos = \App\Models\PagosJuicio::where('user_id', $viejo->id)->whereHas('finanza', fn($q) => $q->where('juicio_id', $j->id))->count();
                // 3. Quitar saliente + historial inmutable
                $j->abogados()->detach($viejo->id);
                \App\Models\JuicioHistorialEstado::create([
                    'juicio_id' => $j->id, 'user_id' => auth()->id(),
                    'estado_procesal_id' => $j->estado_procesal_id,
                    'tipo_movimiento' => 'abogado_reasignado',
                    'referencia_tipo' => 'User', 'referencia_id' => $nuevo->id,
                    'descripcion' => "Reasignado de {$viejo->name} a {$nuevo->name}. Gestión del saliente desde {$desde->format('d/m/Y')}: {$acts} actividades, {$firmas} firmas, {$pagos} abonos.",
                ]);
            }
            \App\Models\Consulta::where('abogado_id', $viejo->id)
                ->whereIn('estado_atencion', ['pendiente', 'atendida'])
                ->update(['abogado_id' => $nuevo->id]);
        });
        $this->requiereReemplazo = false;
        $this->reemplazo_id = null;
        $this->casosAfectados = [];
        $this->dispatchBrowserEvent('noty', ['msg' => 'Casos transferidos. Pulse Guardar para aplicar el cambio.', 'type' => 'success', 'action' => '']);
    }

    public function Destroy(User $user)
    {
        $tieneJuicios = \App\Models\Juicio::whereHas('abogados', fn($q) => $q->where('user_id', $user->id))->exists();
        $tieneConsultas = \App\Models\Consulta::where('abogado_id', $user->id)->whereIn('estado_atencion', ['pendiente','atendida'])->exists();
        if ($user->sales->count() < 1 && $user->especialidades->count() < 1 && !$tieneJuicios && !$tieneConsultas) {     
            $user->especialidades()->detach();
            $user->delete();
            $this->noty("El usuario <b>$user->name</b> fue eliminado del sistema");
        } else{
            $this->noty('no es posible eliminar el usuario, tiene ventas, especialidades o casos asignados', 'noty', false, 'error');
        }
    }
}
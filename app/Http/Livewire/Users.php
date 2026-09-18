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
        'firma_archivo', 'firma_password', 'tieneFirma');  // ← AGREGADO tieneFirma
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

    public function Destroy(User $user)
    {
        if ($user->sales->count() < 1 && $user->especialidades->count() < 1) {     
            $user->especialidades()->detach();
            $user->delete();
            $this->noty("El usuario <b>$user->name</b> fue eliminado del sistema");
        } else{
            $this->noty('no es posible eliminar el usuario, tiene ventas o especialidades asociadas');
        }
    }
}
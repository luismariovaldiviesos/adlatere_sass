<?php

namespace App\Http\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use App\Models\Juicio;
use App\Models\Asunto;
use App\Models\Materia;
use App\Models\Procedimiento;
use App\Models\EstadoProcesal;
use Carbon\Carbon;

class Juicios extends Component
{

 use WithPagination , WithFileUploads;


     public $action = 'Listado', $componentName = 'Juicios', $search = '', $form = false, $selected_id = 0;
    private $pagination =10;
    protected $paginationTheme = 'tailwind';

    //variables juicios
    public $cod_satje, $asunto_id, $estado_procesal_id, $fecha_inicio, $prioridad = 'Baja';

    // variables para todas las pestañas
       //alumno para todas las fichas
    public $juicio;
    // Propiedades para los selects
    public $materias = [], $procedimientos = [], $asuntos = [];
    // Propiedades para los IDs seleccionados
    public $materia_id, $procedimiento_id;

    // variables para las pesteñas
      public string $tab = 'juicio', $fecha_nacimiento;
      public $editModeLesion = false;

     // --- BÚSQUEDA Y SELECCIÓN ---
     public $showDropdown = false; // Variable mágica
    public $searchCustomer = '';
    public $customers = [];
    public $cliente_id = null;
    public $juicio_id = null;
    public $cliente_nombre = '';
    public $rol = ''; // 'ACTOR', 'DEMANDADO', 'TERCERO'
    // --- CREACIÓN RÁPIDA (QUICK CUSTOMER) ---
    public $showCreateCustomer = false; 
    public $q_businame, $q_valueidenti, $q_typeidenti = 'cedula', $q_address, $q_phone, $q_email;



    // 

    public function mount()
    {
        // Solo cargamos Materias al inicio. Es una lista corta y fija.
        $this->materias = Materia::orderBy('nombre', 'asc')->get();
        $this->estados_procesales = EstadoProcesal::orderBy('id', 'asc')->get();
        
        if($this->selected_id > 0) {
            $this->edit($this->selected_id);
        }
    }

    // buscador dinamico para sujetos procesales
    public function updatedSearchCustomer($value){

     $this->cliente_id = null; // Reiniciar selección al teclear
    $this->showDropdown = true; // Forzar que se ABRA

        if(strlen($value) > 0){
            $this->customers =  \App\Models\Customer::where('businame','like',"%{$value}%")
            ->orWhere('valueidenti', 'like', "%{$value}%")
            ->orderBy('businame', 'asc')->limit(5)->get();
        } else{ $this->customers = []; }

    }

    // 2. Al dar clic en la lista flotante
    public function selectCustomer($id, $name)
    {
        $this->cliente_id = $id;
        $this->cliente_nombre = $name;
        $this->searchCustomer = $name; // Deja el nombre en el input
        $this->customers = []; // Cierra la lista flotante
         $this->showDropdown = false; // FORZAR QUE SE CIERRE LA CAJA
    }

    // para actualizar materia buscamos los procedimientos relacionados
    public function updatedMateriaId($value)
    {
        $this->procedimientos = Procedimiento::where('materia_id', $value)->orderBy('nombre', 'asc')->get();
        //reseteamos los hijos para que no queden huerfanos
        $this->procedimiento_id = null;
        $this_asunto_id = null;
        $this->asuntos = [];
    }

    //al cambiar de procedimiento buscamos los asuntos relacionados
    public function updatedProcedimientoId($value)
    {
        $this->asuntos = Asunto::where('procedimiento_id', $value)->orderBy('nombre', 'asc')->get();
        //reseteamos los hijos para que no queden huerfanos
        $this->asunto_id = null;
    }


    public function render()
    {
        $info = Juicio::with('asunto.procedimiento.materia') // Carga la relación automáticamente
            ->where('cod_satje', 'like', "%{$this->search}%")
            ->orWhereHas('asunto', function($query) { // Permite buscar también por el nombre del asunto
                $query->where('nombre', 'like', "%{$this->search}%");
            })
            ->orderBy('id', 'desc') // Los juicios más nuevos primero
            ->paginate($this->pagination);

        return view('livewire.juicios.component', [
            'juicios' => $info
        ])->layout('layouts.theme.app');
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

        public function resetUI()
    {
        $this->resetPage();
        $this->resetValidation();
        $this->reset('cod_satje','asunto_id','estado_procesal_id','fecha_inicio','prioridad','selected_id','search');
    }

    public function saveJuicio(){
        $this->validate(Juicio::rules($this->selected_id), Juicio::messages());
        //dd($this->cod_satje, $this->asunto_id, $this->estado_procesal_id, $this->fecha_inicio, $this->prioridad);
       $juicio =  Juicio::updateOrCreate(['id' => $this->selected_id], [
            'cod_satje' => $this->cod_satje,
            'asunto_id' => $this->asunto_id,
            'estado_procesal_id' => $this->estado_procesal_id,
            'fecha_inicio' => $this->fecha_inicio,
            'prioridad' => $this->prioridad ?? 'Baja'
        ]);
        $this->selected_id = $juicio->id;
        // Llamamos a noty con reset = false para que NO cierre el formulario
        $this->noty($this->selected_id ? 'Juicio actualizado' : 'Juicio registrado', 'noty', false);
        $this->tab = 'ficha'; // avanzar a Ficha
    }

    public function addParticipante(){
        //dd($this->selected_id, $this->cliente_id, $this->rol);
        
        if(!$this->selected_id || $this->selected_id <= 0){
            $this->noty( 'Debe guardar el juicio primero.', 'noty', false);
             $this->tab = 'juicio'; // volver a la pestaña de juicio para guardar primero
            return;           
        }
            if(!$this->cliente_id || $this->cliente_id <= 0){
                $this->noty( 'Seleccione un sujeto procesal válido.', 'noty', false);
                return;           
            }
    
            if(!$this->rol || !in_array($this->rol, ['actor', 'demandado', 'tercero'])){
                $this->noty( 'Seleccione un rol válido para el participante.', 'noty', false);
                return;           
            }

        $juicio =  Juicio::find($this->selected_id);
        if($juicio->participantes()->where('customer_id', $this->cliente_id)->exists()){
            $this->noty( 'Este sujeto ya es participante en el juicio.', 'noty', false);
            return; 
        }
        $juicio->participantes()->attach($this->cliente_id, ['rol' => $this->rol]);
         $this->noty('Sujeto procesal agregado con éxito.', 'noty', false);
          // Limpiamos los cajones para agregar otro
        $this->reset(['cliente_id', 'cliente_nombre', 'searchCustomer', 'rol', 'customers']);
        $this->customers = [];

    }


    public function showCreateCustomer(){
         $this->showDropdown = false; // CIERRA INMEDIATAMENTE LA CAJA AL DAR CLIC
        $this->showCreateCustomer = true;
        $this->reset(['q_businame', 'q_valueidenti', 'q_address', 'q_phone', 'q_email']);
        $this->q_typeidenti = 'cedula';
         // Autocompletamos lo que estaba intentando buscar
        if(is_numeric($this->searchCustomer)) {
            $this->q_valueidenti = $this->searchCustomer;
        } else {
            $this->q_businame = $this->searchCustomer;
        }

        $this->dispatchBrowserEvent('open-modal-quick-customer');      
        
    }


    public function saveQuickCustomer(){
        //dd($this->q_businame, $this->q_valueidenti, $this->q_typeidenti, $this->q_address, $this->q_phone, $this->q_email);
        $this->validate([
        'q_businame' => 'required|min:3',
        'q_valueidenti' => 'required|unique:customers,valueidenti',
        'q_typeidenti' => 'required',
        ]);

        try {
        $customer = \App\Models\Customer::create([
            'businame' => $this->q_businame,
            'valueidenti' => $this->q_valueidenti,
            'typeidenti' => $this->q_typeidenti,
            'address' => $this->q_address ?? 'S/N',
            'email' => $this->q_email ?? $this->q_valueidenti . '@adlatere.ec',
             'phone' => $this->q_phone ?? '0000000000',
             'notes' => 'Creado desde Juicios',
        ]);
         // Lo autoseleccionamos para el juicio
        $this->selectCustomer($customer->id, $customer->businame);
        $this->dispatchBrowserEvent('close-modal-quick-customer');
        $this->noty('Sujeto registrado y seleccionado.', 'noty', false);
        } catch (\Exception $e) {
            $this->noty('Error al crear el cliente: ' . $e->getMessage(), 'noty', false, 'error');
        }
    }


    public function Edit(Juicio $juicio){
        //dd($juicio->asunto->procedimiento->materia->nombre);
        $this->selected_id = $juicio->id;
        //dd($this->selected_id);
        $this->cod_satje = $juicio->cod_satje;
        $this->asunto_id = $juicio->asunto_id;
        $this->estado_procesal_id = $juicio->estado_procesal_id;
        $this->fecha_inicio = Carbon::parse($juicio->fecha_inicio)->format('Y-m-d');
        $this->prioridad = $juicio->prioridad;
        // $this->materia = $juicio->asunto->procedimiento->materia->nombre;
        // $this->procedimiento = $juicio->asunto->procedimiento->nombre;
        // $this->asunto = $juicio->asunto->nombre;
        $this->materia_id = $juicio->asunto->procedimiento->materia_id;
       $this->procedimientos = Procedimiento::where('materia_id', $this->materia_id)->orderBy('nombre', 'asc')->get();
        $this->procedimiento_id = $juicio->asunto->procedimiento_id;
        $this->asuntos = Asunto::where('procedimiento_id', $this->procedimiento_id)->orderBy('nombre', 'asc')->get();
        $this->asunto_id = $juicio->asunto_id;
         $this->action = 'Editar';
        $this->form = true;
    }


    public function removeParticipante($id){
        $juicio = Juicio::find($this->selected_id);
        $juicio->participantes()->detach($id);
        $this->noty('Sujeto procesal removido con éxito.', 'noty', false);
    }


}


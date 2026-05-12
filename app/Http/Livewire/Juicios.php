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
    public $provincias = [], $cantones = [], $unidades_judiciales = [];
    public $materias = [], $procedimientos = [], $asuntos = [];
    
    // Propiedades para los IDs seleccionados
    public $provincia_id, $canton_id, $unidad_id;
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
    // --- EDICIÓN DE PARTICIPANTE ---
    public $editModeSujeto = false;
    public $editModeJuicio = false;


    //propiedades para las actividades
    public $actividades = [];
    public $tipo_actividad_id, $origen = 'Interno', $fecha_actividad, $descripcion, $contenido, $nuevo_estado_id;
    public $archivo;
    public $tiene_plantilla_disponible = false;
    public $plantillas_disponibles = [];
    public $plantilla_seleccionada_id = null;
    public $editModeActividad = false, $selected_actividad_id;
    public $estados_procesales = [];




    // 

    public function mount()
    {
        $this->provincias = \App\Models\Provincia::orderBy('nombre', 'asc')->get();
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

    public function updatedProvinciaId($value)
    {
        $this->cantones = \App\Models\Canton::where('provincia_id', $value)->orderBy('nombre', 'asc')->get();
        $this->canton_id = null;
        $this->unidad_id = null;
        $this->unidades_judiciales = [];
    }

    public function updatedCantonId($value)
    {
        $this->unidades_judiciales = \App\Models\Unidad::where('canton_id', $value)->orderBy('nombre', 'asc')->get();
        $this->unidad_id = null;
    }

    // para actualizar materia buscamos los procedimientos relacionados
    public function updatedMateriaId($value)
    {
        $this->procedimientos = Procedimiento::where('materia_id', $value)->orderBy('nombre', 'asc')->get();
        //reseteamos los hijos para que no queden huerfanos
        $this->procedimiento_id = null;
        $this->asunto_id = null;
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
            'juicios' => $info,
            'tipos_actividades' => \App\Models\TipoActividad::orderBy('nombre', 'asc')->get(),
           'lista_actividadades' => \App\Models\Actividad::orderBy('fecha_actividad', 'desc')->get()
        ])->layout('layouts.theme.app');
    }



    public function noty($msg, $eventName = 'noty', $reset = true, $action =""){
        $this->dispatchBrowserEvent($eventName, ['msg'=>$msg, 'type' => 'success', 'action' => $action ]);
        if($reset) $this->resetUI();
    }

       public function  addNew()
    {
        $this->resetUI();
        $this->editModeJuicio = false;
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
        $this->reset('cod_satje','asunto_id','unidad_id','provincia_id','canton_id','materia_id','procedimiento_id','estado_procesal_id','fecha_inicio','prioridad','selected_id','search');
        $this->cantones = [];
        $this->unidades_judiciales = [];
        $this->procedimientos = [];
        $this->asuntos = [];
    }

    public function saveJuicio(){
        $this->validate(Juicio::rules($this->selected_id), Juicio::messages());
        
       $juicio =  Juicio::updateOrCreate(['id' => $this->selected_id], [
            'cod_satje' => $this->cod_satje,
            'asunto_id' => $this->asunto_id,
            'unidad_id' => $this->unidad_id,
            'estado_procesal_id' => $this->estado_procesal_id,
            'fecha_inicio' => $this->fecha_inicio,
            'prioridad' => $this->prioridad ?? 'Baja'
        ]);
        
        $this->juicio = Juicio::with(['asunto.procedimiento.materia', 'unidadJudicial.canton.provincia', 'actores', 'demandados', 'estadoProcesal'])->find($juicio->id);
        $this->selected_id = $juicio->id;
        // Mensaje dinámico según el modo
        // RE-HIDRATAR las propiedades para que la vista las vea actualizadas
        $this->cod_satje = $juicio->cod_satje;
        $this->fecha_inicio = \Carbon\Carbon::parse($juicio->fecha_inicio)->format('Y-m-d');
        $this->prioridad = $juicio->prioridad;
        $this->estado_procesal_id = $juicio->estado_procesal_id;
        
        // Sincronizar selectores jerárquicos
        $this->unidad_id = $juicio->unidad_id;
        $this->canton_id = $juicio->unidadJudicial->canton_id ?? null;
        $this->provincia_id = $juicio->unidadJudicial->canton->provincia_id ?? null;
        if($this->provincia_id) $this->cantones = \App\Models\Canton::where('provincia_id', $this->provincia_id)->orderBy('nombre', 'asc')->get();
        if($this->canton_id) $this->unidades_judiciales = \App\Models\Unidad::where('canton_id', $this->canton_id)->orderBy('nombre', 'asc')->get();

        $this->materia_id = $juicio->asunto->procedimiento->materia_id;
        $this->procedimiento_id = $juicio->asunto->procedimiento_id;
        $this->asunto_id = $juicio->asunto_id;
        if($this->materia_id) $this->procedimientos = Procedimiento::where('materia_id', $this->materia_id)->orderBy('nombre', 'asc')->get();
        if($this->procedimiento_id) $this->asuntos = Asunto::where('procedimiento_id', $this->procedimiento_id)->orderBy('nombre', 'asc')->get();

         $this->editModeJuicio = true;

        $mensaje = $this->editModeJuicio ? 'Juicio actualizado' : 'Juicio registrado';
        $this->noty($mensaje, 'noty', false);
        // Si era nuevo, ahora pasamos a modo edición por si quiere seguir editando la carátula
       
        //$this->tab = 'ficha'; // avanzar a Ficha
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
        $this->reset(['cliente_id', 'searchCustomer', 'rol', 'customers']);
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
        $this->juicio = Juicio::with(['asunto.procedimiento.materia', 'unidadJudicial.canton.provincia', 'actores', 'demandados', 'estadoProcesal'])->find($juicio->id);
        
        $this->selected_id = $juicio->id;
        $this->cod_satje = $juicio->cod_satje;
        $this->asunto_id = $juicio->asunto_id;
        $this->estado_procesal_id = $juicio->estado_procesal_id;
        $this->fecha_inicio = Carbon::parse($juicio->fecha_inicio)->format('Y-m-d');
        $this->prioridad = $juicio->prioridad;
        
        $this->unidad_id = $juicio->unidad_id;
        $this->canton_id = $juicio->unidadJudicial->canton_id ?? null;
        $this->provincia_id = $juicio->unidadJudicial->canton->provincia_id ?? null;
        if($this->provincia_id) $this->cantones = \App\Models\Canton::where('provincia_id', $this->provincia_id)->orderBy('nombre', 'asc')->get();
        if($this->canton_id) $this->unidades_judiciales = \App\Models\Unidad::where('canton_id', $this->canton_id)->orderBy('nombre', 'asc')->get();

        $this->materia_id = $juicio->asunto->procedimiento->materia_id;
       $this->procedimientos = Procedimiento::where('materia_id', $this->materia_id)->orderBy('nombre', 'asc')->get();
        $this->procedimiento_id = $juicio->asunto->procedimiento_id;
        $this->asuntos = Asunto::where('procedimiento_id', $this->procedimiento_id)->orderBy('nombre', 'asc')->get();
        $this->asunto_id = $juicio->asunto_id;
        $this->editModeJuicio = true;
         $this->action = 'Editar';       
        $this->form = true;
    }


    public function editParticipante($id){
        //dd($id);
            $participante = \App\Models\Customer::find($id);
            $pivotData = $participante->juicios()->where('juicio_id', $this->selected_id)->first()->pivot;
            $this->cliente_id = $participante->id;
            $this->searchCustomer = $participante->businame;
            $this->rol = $pivotData->rol;
            $this->editModeSujeto = true;
            //dd($this->cliente_id, $this->searchCustomer, $this->rol);
             $this->noty('lesion cargada para editar', 'noty', false);
    }


    public function removeParticipante($id){
        $juicio = Juicio::find($this->selected_id);
        $juicio->participantes()->detach($id);
        $this->noty('Sujeto procesal removido con éxito.', 'noty', false);
    }

   public function  editParticipanteEnJuicio($id){
        $juicio = Juicio::find($this->selected_id);
        $juicio->participantes()->updateExistingPivot($id, ['rol' => $this->rol]);
        $this->noty('Rol del sujeto procesal actualizado con éxito.', 'noty', false);
        // Limpiamos los cajones para agregar otro
        $this->reset(['cliente_id', 'searchCustomer', 'rol', 'customers']);
        $this->editModeSujeto = false;
         $this->customers = [];
   }

   // mtodos actividades 
   public function updatedTipoActividadId($value){
    $this->tiene_plantilla_disponible = false;
    $this->plantillas_disponibles = [];
    $this->plantilla_seleccionada_id = null;
    
    // Limpiar el contenido del editor al cambiar de actividad
    $this->contenido = '';
    $this->dispatchBrowserEvent('set-actividad-editor-content', ['content' => '']);

    if($value){
        $plantillas = \App\Models\PlantillaTipoActividad::where('tipo_actividad_id', $value)->where('activo', true)->get();
        if($plantillas->count() > 0){
            $this->tiene_plantilla_disponible = true;
            $this->plantillas_disponibles = $plantillas;
            
            // Si solo hay una plantilla, la pre-seleccionamos
            if($plantillas->count() == 1){
                $this->plantilla_seleccionada_id = $plantillas->first()->id;
            }
        }
    }
   }

   private function reemplazarVariables($textoHtml) {
       $juicio = Juicio::with(['unidadJudicial.canton.provincia', 'asunto.procedimiento.materia', 'actores', 'demandados'])->find($this->selected_id);
       if(!$juicio) return $textoHtml;

       $actores = $juicio->actores->pluck('businame')->implode(', ');
       $demandados = $juicio->demandados->pluck('businame')->implode(', ');

       $variables = [
           'COD_SATJE' => $juicio->cod_satje ?? '',
           'FECHA_INICIO' => $juicio->fecha_inicio ? \Carbon\Carbon::parse($juicio->fecha_inicio)->format('d/m/Y') : '',
           'MATERIA' => $juicio->asunto->procedimiento->materia->nombre ?? '',
           'PROCEDIMIENTO' => $juicio->asunto->procedimiento->nombre ?? '',
           'ASUNTO' => $juicio->asunto->nombre ?? '',
           'UNIDAD_JUDICIAL' => $juicio->unidadJudicial->nombre ?? '',
           'CIUDAD' => $juicio->unidadJudicial->canton->nombre ?? '',
           'PROVINCIA' => $juicio->unidadJudicial->canton->provincia->nombre ?? '',
           'ACTORES' => $actores,
           'DEMANDADOS' => $demandados,
           'FECHA_ACTUAL' => \Carbon\Carbon::now()->translatedFormat('d \d\e F \d\e Y'),
       ];

       foreach($variables as $key => $value) {
           // Soporta [VARIABLE], {VARIABLE}, [variable], {variable}
           $textoHtml = str_ireplace('[' . $key . ']', $value, $textoHtml);
           $textoHtml = str_ireplace('{' . $key . '}', $value, $textoHtml);
       }

       return $textoHtml;
   }

   public function cargarPlantilla(){
       if($this->plantilla_seleccionada_id){
            $plantilla = \App\Models\PlantillaTipoActividad::find($this->plantilla_seleccionada_id);
            if($plantilla){
                $this->contenido = $this->reemplazarVariables($plantilla->contenido);
                $this->dispatchBrowserEvent('set-actividad-editor-content', ['content' => $this->contenido]);
                $this->noty('Plantilla cargada ('.strlen($this->contenido).' caracteres)', 'noty', false);
            }
       } else {
           $this->noty('Por favor seleccione una plantilla primero', 'noty', false);
       }
   }

  

   public function addActividad(){

    $this->validate([
        'tipo_actividad_id' => 'required',
        'fecha_actividad' => 'required',
        'origen' => 'required'
    ]);

    $archivoPath = null;
    if ($this->archivo) {
        $archivoPath = $this->archivo->store('actividades_juicios', 'public');
    }

    // Registro de la actividad vinculada al juicio actual
    $actividad = \App\Models\Actividad::create([
        'juicio_id' => $this->selected_id,
        'tipo_actividad_id' => $this->tipo_actividad_id,
        'user_id' => auth()->id(),
        'origen' => $this->origen,
        'fecha_actividad' => $this->fecha_actividad,
        'descripcion' => $this->descripcion,
        'contenido' => $this->contenido,
        'archivo' => $archivoPath // asegurando que el modelo tenga este campo si existe
    ]);

    // MEJORA: Si se seleccionó un nuevo estado, actualizar la carátula del juicio
    if ($this->nuevo_estado_id) {
        $juicio = Juicio::find($this->selected_id);
        $juicio->update(['estado_procesal_id' => $this->nuevo_estado_id]);
        $this->estado_procesal_id = $this->nuevo_estado_id; // Sincronizar UI
    }

    $this->noty('Actividad registrada y juicio actualizado', 'noty', false);
    $this->resetActividadInputs();

   }

   public function resetActividadInputs(){
    $this->reset(['tipo_actividad_id', 'descripcion', 'contenido', 'nuevo_estado_id', 'archivo', 'tiene_plantilla_disponible', 'plantillas_disponibles', 'plantilla_seleccionada_id']);
    $this->dispatchBrowserEvent('set-actividad-editor-content', ['content' => '']);
   }


}


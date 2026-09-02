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
use App\Models\Audiencia;
use Carbon\Carbon;
use Iluminate\Support\Facades\Response;
use Barryvdh\DomPDF\Facade\Pdf;

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


    // propiedades para las audiencias 
    public $audiencia_id = null;
    public $aud_fecha_hora, $aud_tipo_audiencia, $aud_sala_enlace;
    public $aud_estado = 'Programada', $aud_acta_resumen;
    public $editModeAudiencia = false;

 // Propiedades para Documentos
    public $doc_nombre;
    public $doc_archivo;
    public $doc_origen_tipo = 'General'; // Valor por defecto
    public $search_doc = ''; // Buscador de documentos


    //variables para finanzas
    public $fin_honorarios = 0, $fin_gastos = 0, $fin_notas_acuerdo;
    public $pago_customer_id, $pago_monto, $pago_fecha, $pago_metodo = 'Transferencia';
    public    $pago_referencia, $pago_notas, $pago_comprobante;


    //propiedades de funcionarios
    public $searchFuncionario = '';
    public $funcionarios_list = [];
    public $funcionario_id = null;
    public $rol_en_juicio = '';
    public $showFuncionarioDropdown = false;





    // 

    public function mount()
    {
        $this->provincias = \App\Models\Provincia::orderBy('nombre', 'asc')->get();
        // Solo cargamos Materias al inicio. Es una lista corta y fija.
        $this->materias = Materia::orderBy('nombre', 'asc')->get();
        $this->estados_procesales = EstadoProcesal::orderBy('id', 'asc')->get();
        
        if($this->selected_id > 0) {
            $this->edit(\App\Models\Juicio::find($this->selected_id));
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
        
        $this->juicio = Juicio::with(['asunto.procedimiento.materia', 
        'unidadJudicial.canton.provincia', 'actores', 'demandados', 'estadoProcesal', 
        'actividades.tipoActividad','finanza.pagos'])->find($juicio->id);
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
        $this->juicio = Juicio::with(['asunto.procedimiento.materia', 'unidadJudicial.canton.provincia', 'actores', 'demandados', 'estadoProcesal', 'actividades.tipoActividad', 'finanza.pagos', 'finanza.pagos.cliente'])->find($juicio->id);
        $finanza  =  \App\Models\FinanzasJuicio::firstOrCreate(['juicio_id' => $juicio->id], 
                    ['honorarios_totales' => 0, 'gastos_extras' => 0]);
        $this->fin_honorarios = $finanza->honorarios_totales;
       $this->fin_gastos = $finanza->gastos_extras;
        $this->fin_notas_acuerdo = $finanza->notas_acuerdo;
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
        // Cargar el juicio con todas las relaciones necesarias para reemplazar las variables
        //en el contenido de la plantilla
       $juicio = Juicio::with(['unidadJudicial.canton.provincia', 'asunto.procedimiento.materia', 
       'actores', 'demandados','funcionarios'])->find($this->selected_id);
       if(!$juicio) return $textoHtml;

       $actores = $juicio->actores->pluck('businame')->implode(', ');
       $demandados = $juicio->demandados->pluck('businame')->implode(', ');
       //juez ponente 
         $juez = $juicio->funcionarios->first(function($f) {
            return strtolower($f->pivot->rol_en_juicio) === 'juez ponente' 
                || strtolower($f->cargo) === 'juez';
        })?->nombre ?? '';

            // 2. Buscar al funcionario asignado como Secretario
        $secretario = $juicio->funcionarios->first(function($f) {
            return strtolower($f->pivot->rol_en_juicio) === 'secretario' 
                || strtolower($f->cargo) === 'secretario';
        })?->nombre ?? '';
        // 3. Formatear la lista completa de todos los funcionarios asignados y sus roles
        $todosFuncionarios = $juicio->funcionarios->map(function($f) {
            return "{$f->nombre} ({$f->pivot->rol_en_juicio})";
        })->implode(', ');

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
             'JUEZ' => $juez,
            'SECRETARIO' => $secretario,
            'FUNCIONARIOS' => $todosFuncionarios,
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

    if ($this->editModeActividad) {
        $actividad = \App\Models\Actividad::find($this->selected_actividad_id);
        $actividad->update([
            'tipo_actividad_id' => $this->tipo_actividad_id,
            'origen' => $this->origen,
            'fecha_actividad' => $this->fecha_actividad,
            'descripcion' => $this->descripcion,
            'contenido' => $this->contenido,
            'archivo' => $archivoPath ? $archivoPath : $actividad->archivo
        ]);
        $this->noty('Actividad actualizada', 'noty', false);
    } else {
        \App\Models\Actividad::create([
            'juicio_id' => $this->selected_id,
            'tipo_actividad_id' => $this->tipo_actividad_id,
            'user_id' => auth()->id(),
            'origen' => $this->origen,
            'fecha_actividad' => $this->fecha_actividad,
            'descripcion' => $this->descripcion,
            'contenido' => $this->contenido,
            'archivo' => $archivoPath 
        ]);
        $this->noty('Actividad registrada', 'noty', false);
    }

    // MEJORA: Si se seleccionó un nuevo estado, actualizar la carátula del juicio
    if ($this->nuevo_estado_id) {
        $juicio = Juicio::find($this->selected_id);
        $juicio->update(['estado_procesal_id' => $this->nuevo_estado_id]);
        $this->estado_procesal_id = $this->nuevo_estado_id; // Sincronizar UI
    }

    // Recargar el juicio para refrescar el listado y el sidebar
    $this->juicio = Juicio::with(['asunto.procedimiento.materia', 'unidadJudicial.canton.provincia', 'actores', 'demandados', 'estadoProcesal', 'actividades.tipoActividad'])->find($this->selected_id);

    $this->resetActividadInputs();

   }

   public function editActividad($id) {
        $actividad = \App\Models\Actividad::find($id);
        $this->selected_actividad_id = $id;
        $this->tipo_actividad_id = $actividad->tipo_actividad_id;
        $this->origen = $actividad->origen;
        $this->fecha_actividad = \Carbon\Carbon::parse($actividad->fecha_actividad)->format('Y-m-d\TH:i');
        $this->descripcion = $actividad->descripcion;
        $this->contenido = $actividad->contenido;
        $this->editModeActividad = true;
        
        $this->dispatchBrowserEvent('set-actividad-editor-content', ['content' => $this->contenido]);
   }

   public function cancelEditActividad() {
        $this->resetActividadInputs();
   }

   public function destroyActividad($id) {
        \App\Models\Actividad::find($id)->delete();
        $this->juicio = Juicio::with(['asunto.procedimiento.materia', 'unidadJudicial.canton.provincia', 'actores', 'demandados', 'estadoProcesal', 'actividades.tipoActividad'])->find($this->selected_id);
        $this->noty('Actividad eliminada', 'noty', false);
        if ($this->selected_actividad_id == $id) {
            $this->resetActividadInputs();
        }
   }

   public function resetActividadInputs(){
    $this->reset(['tipo_actividad_id', 'descripcion', 'contenido', 'nuevo_estado_id', 'archivo', 'tiene_plantilla_disponible', 'plantillas_disponibles', 'plantilla_seleccionada_id', 'editModeActividad', 'selected_actividad_id']);
    $this->dispatchBrowserEvent('set-actividad-editor-content', ['content' => '']);
   }
   

   public function saveAudiencia(){
   
    // $this->validate([
    //     'aud_fecha_hora'     => 'required|date',
    //     'aud_tipo_audiencia' => 'required|string|max:255',
    //     'aud_estado'         => 'required|in:Programada,Realizada,Suspendida,Fallida',
    // ]);
    if ($this->editModeAudiencia) {
        // MODO EDICIÓN
        \App\Models\Audiencia::find($this->audiencia_id)->update([
            'fecha_hora'      => $this->aud_fecha_hora,
            'tipo_audiencia'  => $this->aud_tipo_audiencia,
            'sala_enlace'     => $this->aud_sala_enlace,
            'estado'          => $this->aud_estado,
            'acta_resumen'    => $this->aud_acta_resumen,
        ]);
        $this->noty('Audiencia actualizada correctamente.', 'noty', false);
    } else {
        // MODO CREACIÓN
        // dd($this->selected_id, $this->aud_fecha_hora, $this->aud_tipo_audiencia, $this->aud_sala_enlace, $this->aud_estado, $this->aud_acta_resumen);
        \App\Models\Audiencia::create([
            'juicio_id'       => $this->selected_id,
            'fecha_hora'      => $this->aud_fecha_hora,
            'tipo_audiencia'  => $this->aud_tipo_audiencia,
            'sala_enlace'     => $this->aud_sala_enlace,
            'estado'          => $this->aud_estado,
            'acta_resumen'    => $this->aud_acta_resumen,
        ]);
        $this->noty('Audiencia registrada correctamente.', 'noty', false);
    }
    // Refrescar el modelo para que el listado y sidebar se actualicen
    $this->juicio = \App\Models\Juicio::with([
        'asunto.procedimiento.materia',
        'unidadJudicial.canton.provincia',
        'actores', 'demandados',
        'estadoProcesal',
        'actividades.tipoActividad',
        'audiencias',
    ])->find($this->selected_id);
    $this->resetAudienciaInputs();
   }

   public function editAudiencia($id){
    $aud =  \App\Models\Audiencia::find($id);
    $this->audiencia_id = $id;
    $this->aud_fecha_hora = \Carbon\Carbon::parse($aud->fecha_hora)->format('Y-m-d\TH:i');
    $this->aud_tipo_audiencia = $aud->tipo_audiencia;
    $this->aud_sala_enlace = $aud->sala_enlace;
    $this->aud_estado = $aud->estado;
    $this->aud_acta_resumen = $aud->acta_resumen;
    $this->editModeAudiencia = true;
   }

   public function destroyAudiencia($id){
    \App\Models\Audiencia::find($id)->delete();
    $this->juicio = \App\Models\Juicio::with([
        'asunto.procedimiento.materia',
        'unidadJudicial.canton.provincia',
        'actores', 'demandados',
        'estadoProcesal',
        'actividades.tipoActividad',
        'audiencias',
    ])->find($this->selected_id);
      if ($this->audiencia_id == $id) {
        $this->resetAudienciaInputs();
    }
    $this->noty('Audiencia eliminada correctamente.', 'noty', false);
   }
   public function cancelEditAudiencia()
    {
        $this->resetAudienciaInputs();
    }

    public function resetAudienciaInputs()
{
    $this->reset([
        'audiencia_id', 'aud_fecha_hora', 'aud_tipo_audiencia',
        'aud_sala_enlace', 'aud_acta_resumen', 'editModeAudiencia',
    ]);
    $this->aud_estado = 'Programada'; // valor por defecto
}

// ─────────────────────────────────────────
// DOCUMENTOS (GESTOR GLOBAL)
// ─────────────────────────────────────────

    public function saveDocumentoGeneral(){
        $this->validate([
            'doc_nombre'      => 'required|string|max:255',
            'doc_origen_tipo' => 'required|string|max:255',
            'doc_archivo'     => 'required|file|max:5120', // Máximo 5MB
        ]);


        // Obtener información del archivo
    $extension = $this->doc_archivo->getClientOriginalExtension();
    $pesoKb = filesize($this->doc_archivo->getRealPath()) / 1024;
    // Guardar el archivo físicamente. 
    // NOTA: Como usas stancl/tenancy, el disco 'public' ya se aísla automáticamente en la carpeta de cada tenant.
    $ruta = $this->doc_archivo->store('documentos_juicios', 'public');
    // Registrar en la base de datos
    \App\Models\Documento::create([
        'juicio_id'    => $this->selected_id,
        'origen_tipo'  => $this->doc_origen_tipo, // Aquí guardamos la clasificación seleccionada
        'origen_id'    => null,
        'nombre'       => $this->doc_nombre,
        'ruta_archivo' => $ruta,
        'tipo_archivo' => strtolower($extension),
        'tamaño_archivo'      => $pesoKb,
    ]);
    // Registrar en el historial de auditoría
    \App\Models\JuicioHistorialEstado::create([
        'juicio_id'       => $this->selected_id,
        'user_id'         => auth()->id(),
        'estado_procesal_id' => null, 
        'tipo_movimiento' => 'documento_subido',
        'descripcion'     => 'Se subió un documento (' . $this->doc_origen_tipo . '): ' . $this->doc_nombre,
    ]);
    $this->noty('Documento subido con éxito.', 'noty', false);
    // Limpiar inputs
    $this->reset(['doc_nombre', 'doc_archivo']);
    $this->doc_origen_tipo = 'General';
    
    // Refrescar modelo
    $this->edit(\App\Models\Juicio::find($this->selected_id));
    }

    public function destroyDocumento($id){
        $doc = \App\Models\Documento::find($id);
        // Eliminar el archivo físicamente
        if (\Storage::disk('public')->exists($doc->ruta_archivo)) {
            \Storage::disk('public')->delete($doc->ruta_archivo);
        }
        // Eliminar el registro de la base de datos
        $doc->delete();
          // Registrar en el historial
        \App\Models\JuicioHistorialEstado::create([
            'juicio_id'       => $this->selected_id,
            'user_id'         => auth()->id(),
            'estado_procesal_id' => null, 
            'tipo_movimiento' => 'documento_eliminado',
            'descripcion'     => 'Se eliminó el documento: ' . $doc->nombre,
        ]);
        $this->noty('Documento eliminado.', 'noty', false);
         $this->edit(\App\Models\Juicio::find($this->selected_id));
    }


    public function saveFinanzas(){
        $this->validate([
        'fin_honorarios' => 'required|numeric|min:0',
        'fin_gastos'     => 'required|numeric|min:0',
        ]);

        \App\Models\FinanzasJuicio::updateOrCreate(
            ['juicio_id' => $this->selected_id],
            [
                'honorarios_totales' => $this->fin_honorarios, 
                'gastos_extras'      => $this->fin_gastos, 
                'notas_acuerdo'      => $this->fin_notas_acuerdo
            ]
        );

        \App\Models\JuicioHistorialEstado::create([
        'juicio_id'       => $this->selected_id,
        'user_id'         => auth()->id(),
        'tipo_movimiento' => 'finanzas_actualizadas',
        'descripcion'     => 'Se actualizaron los honorarios y gastos del caso.',
    ]);

    $this->noty('Costos del juicio actualizados.', 'noty', false);
    $this->edit(\App\Models\Juicio::find($this->selected_id));

    }

    public function savePago(){
        $this->validate([
        'pago_customer_id' => 'required',
        'pago_monto'       => 'required|numeric|min:0.01',
        'pago_fecha'       => 'required|date',
        'pago_metodo'      => 'required|string',
    ]);

    $finanza  = \App\Models\FinanzasJuicio::where('juicio_id', $this->selected_id)->first();
    if(!$finanza ) {
       $this->noty('Primero debe configurar los honorarios del caso.', 'noty', false, 'error');
        return;
    }

    $ruta_comprobante = null;
    $extension = null;
    $pesoKb = null;
     // Subir archivo al Gestor Documental silenciósamente
    if ($this->pago_comprobante) {
        $extension = $this->pago_comprobante->getClientOriginalExtension();
        $pesoKb = filesize($this->pago_comprobante->getRealPath()) / 1024;
        $ruta_comprobante = $this->pago_comprobante->store('documentos_juicios', 'public');
    }

       $pago = \App\Models\PagosJuicio::create([
        'finanzas_juicios_id'    => $finanza->id,
        'customer_id'            => $this->pago_customer_id,
        'user_id'                => auth()->id(),
        'monto'                  => $this->pago_monto,
        'fecha_pago'             => $this->pago_fecha,
        'metodo_pago'            => $this->pago_metodo,
        'referencia_transaccion' => $this->pago_referencia,
        'comprobante_ruta'       => $ruta_comprobante,
        'notas'                  => $this->pago_notas,
        'estado'                 => 'Aprobado',
    ]);

    // si hubo compraboante registramos también en documentos para que quede en el gestor documental del juicio
    if($ruta_comprobante){
        \App\Models\Documento::create([
            'juicio_id'    => $this->selected_id,
            'origen_tipo'  => 'Finanzas',
            'origen_id'    => $pago->id, // Vinculado al pago
            'nombre'       => 'Comprobante de Pago: $ ' . $this->pago_monto . ' (' . $this->pago_metodo . ')',
            'ruta_archivo' => $ruta_comprobante,
            'tipo_archivo' => strtolower($extension),
            'peso_kb'      => $pesoKb,
        ]);
    }

      \App\Models\JuicioHistorialEstado::create([
        'juicio_id'       => $this->selected_id,
        'user_id'         => auth()->id(),
        'tipo_movimiento' => 'pago_registrado',
        'descripcion'     => 'Se registró un abono por $ ' . number_format($this->pago_monto, 2),
    ]);

     $this->noty('Abono registrado con éxito.', 'noty', false);
      // Limpiar formulario
    $this->reset(['pago_customer_id', 'pago_monto', 'pago_fecha', 'pago_referencia', 'pago_notas', 'pago_comprobante']);
    $this->pago_metodo = 'Transferencia';
    $this->edit(\App\Models\Juicio::find($this->selected_id));

    }

    public function destroyPago($id)
    {
        $pago = \App\Models\PagosJuicio::find($id);
        
        // Si tenía comprobante, buscamos el registro en Documentos y lo borramos físicamente
        if($pago->comprobante_ruta) {
            if (\Storage::disk('public')->exists($pago->comprobante_ruta)) {
                \Storage::disk('public')->delete($pago->comprobante_ruta);
            }
            \App\Models\Documento::where('origen_tipo', 'Finanzas')->where('origen_id', $pago->id)->delete();
        }
        $pago->delete();
        \App\Models\JuicioHistorialEstado::create([
            'juicio_id'       => $this->selected_id,
            'user_id'         => auth()->id(),
            'tipo_movimiento' => 'pago_eliminado',
            'descripcion'     => 'Se eliminó un abono por $ ' . number_format($pago->monto, 2),
        ]);
        $this->noty('Abono eliminado.', 'noty', false);
        $this->edit(\App\Models\Juicio::find($this->selected_id));
    }   



    //se ejecuta al teclear el buscador de funcionarios 
    public function updatedSearchFuncionario($value){

    $this->funcionario_id = null; //reseteamos el id del funcionario seleccionado
    $this->showFuncionarioDropdown  = true; 
    // 1. Validar por seguridad en el backend que exista una unidad seleccionada
    if (!$this->unidad_id) {
        $this->funcionarios_list = [];
        return;
    }
        if(strlen($value)>0){
            $this->funcionarios_list = \App\Models\Funcionario::where('nombre', 'like', "%$value%")
             // 2. Aquí está la magia: Filtramos solo los funcionarios de esta unidad
              ->whereHas('unidades', function($query) {
                $query->where('unidad_id', $this->unidad_id); // $this->unidad_id viene de la pestaña carátula
            })
            ->orderBy('nombre','asc')
            ->limit(5)
            ->get();
        } else {
            $this->funcionarios_list = [];

        }
    }

    //se ejecuta cuando al hacer clic en un funcionario de la lista desplegable
    public function selectFuncionario($id, $name){
        $this->funcionario_id = $id;
        $this->searchFuncionario = $name;
         $this->funcionarios_list = [];
        $this->showFuncionarioDropdown  = false; 
    }

    //metodo para asignar un funcionario a un juicio
    public function addFuncionario(){
        if(!$this->selected_id || $this->selected_id <=0){
            $this->noty('Debe guardar el juicio primero.', 'noty', false);
             $this->tab = 'juicio'; // volver a la pestaña de juicio para guardar primero
            return;           
        }
        if (!$this->funcionario_id || $this->funcionario_id <= 0)
        {
            $this->noty('Seleccione un funcionario válido.', 'noty', false);
            return;           
        }
         if (empty($this->rol_en_juicio)) {
        $this->noty('Seleccione o escriba el rol del funcionario en el juicio.', 'noty', false);
        return;
        }

        $juicio =  \App\Models\Juicio::find($this->selected_id);
        //validar duplicados 
        if($juicio->funcionarios()->where('funcionario_id', $this->funcionario_id)->exists()){
            $this->noty( 'Este funcionario ya está asignado al juicio.', 'noty', false);
            return; 
        }
        //guardar en la tabla pivote
        $juicio->funcionarios()->attach($this->funcionario_id, ['rol_en_juicio' => $this->rol_en_juicio]);
        $this->noty('Funcionario asignado con éxito.', 'noty', false);
           // Resetear campos
        $this->reset(['funcionario_id', 'searchFuncionario', 'rol_en_juicio', 'funcionarios_list']);
    }

    public function removeFuncionario($funcionarioId){
        if (!$this->selected_id || $this->selected_id <= 0) return;
        $juicio = \App\Models\Juicio::find($this->selected_id);
        $juicio->funcionarios()->detach($funcionarioId);
        $this->noty('Funcionario removido del juicio.', 'noty', false);
    }

    //descargar acta de word
    public function descargarWord(Audiencia $audiencia){
        //dd($audiencia->acta_resumen);
        $contenido = $audiencia->acta_resumen;
        $documentoWord = "<html xmlns:o='urn:schemas-microsoft-com:office:office' xmlns:w='urn:schemas-microsoft-com:office:word' xmlns='http://www.w3.org/TR/REC-html40'>
                    <head><meta charset='utf-8'><title>Acta Resumen</title></head>
                    <body style='font-family: Arial, sans-serif;'>
                        {$contenido}
                    </body>
                    </html>";
        //dd($documentoWord);
        $nombreArchivo =  "Acta audiencia_" . $audiencia->id . "_" . now()->format('dmY') . ".docx";

        // 3. Retornar la descarga forzando las cabeceras HTTP de Word
    return response()->streamDownload(function () use ($documentoWord) {
        echo $documentoWord;
    }, $nombreArchivo, [
        'Content-Type' => 'application/msword',
        'Cache-Control' => 'max-age=0, no-cache, must-revalidate, proxy-revalidate'
    ]);      

    }
    
}


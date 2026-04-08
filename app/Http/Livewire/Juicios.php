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
}


<?php

namespace App\Http\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use App\Models\Juicio;
use App\Models\Asunto;

class Juicios extends Component
{

 use WithPagination , WithFileUploads;


     public $action = 'Listado', $componentName = 'Juicios', $search = '', $form = false, $selected_id = 0;
    private $pagination =10;
    protected $paginationTheme = 'tailwind';

    //variables juicios
    public $cod_satje, $asunto_id, $estado_procesal_id, $fecha_inicio, $prioridad;

    // variables para todas las pestañas
       //alumno para todas las fichas
    public $juicio, $asuntos;

    // variables para las pesteñas
      public string $tab = 'juicio', $fecha_nacimiento;
      public $editModeLesion = false;



    // 

     public  function mount(){
        $this->asuntos = Asunto::orderBy('nombre', 'asc')->get();
        if($this->selected_id >0 ){
            // $this->cargarLesiones($this->selected_id);
            // $this->loadRepresentante();
        }
    }


  public function render()
{
    $info = Juicio::with('asunto') // Carga la relación automáticamente
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
        $this->reset('cod_satje','asunto_id','estado_procesal_id','fecha_inicio','prioridad','selected_id','search','form');
    }
}


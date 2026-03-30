<?php

namespace App\Http\Livewire;


use Livewire\Component;
use App\Models\EstadoProcesal;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use App\Models\Fase;

class EstadosProcesales extends Component

{
    use WithPagination;
    use WithFileUploads;


    public $nombre = '', $fase_id, $descripcion="", $fase,  $selected_id = 0;
    public $action = 'Listado', $componentName = 'Listado de Estados Procesales', $search, $form = false;
    private $pagination = 10;
    protected $paginationTheme = 'tailwind';

     public function render()
    {
        if (strlen($this->search) > 0)
            $info = EstadoProcesal::where('nombre', 'like', "%{$this->search}%")->paginate($this->pagination);
        else
            $info = EstadoProcesal::paginate($this->pagination);


        return view('livewire.estados_procesales.component', ['estados' => $info, 'fases' => Fase::orderBy('nombre', 'asc')->get()])
            ->layout('layouts.theme.app');
    }

    public $listeners = [
        'resetUI',
        'Destroy'
    ];

    public function updatedForm()
    {
        if($this->selected_id > 0)
            $this->action ='Editar';
        else
            $this->action ='Agregar';

    }

    public function noty($msg, $eventName = 'noty', $reset = true, $action = '')
    {
        $this->dispatchBrowserEvent($eventName, ['msg' => $msg, 'type' => 'success', 'action' => $action]);
        if ($reset) $this->resetUI();
    }
    public function addNew()
    {
        $this->resetUI();
        $this->form = true;
        $this->action = 'Agregar';
    }


    public function CloseModal()
    {
        $this->resetUI();
        $this->noty(null, 'close-modal');
    }

    public function resetUI()
    {
        // limpiar mensajes rojos de validación
        $this->resetValidation();
        // regresar a la página inicial del componente
        $this->resetPage();
        // regresar propiedades a su valor por defecto
        $this->reset('nombre', 'fase_id', 'selected_id', 'search', 'action', 'componentName', 'form','fase', 'descripcion');
    }

    public function Edit(EstadoProcesal $estado)
    {
        $this->selected_id = $estado->id;
        $this->nombre = $estado->nombre;
        $this->fase_id =  $estado->fase_id;
        $this->descripcion =  $estado->descripcion;
        $this->action = 'Editar';
        $this->form = true;

    }

    public function Store()
    {
        sleep(1);

        $this->validate(EstadoProcesal::rules($this->selected_id), EstadoProcesal::$messages);

        $estado = EstadoProcesal::updateOrCreate(
            ['id' => $this->selected_id],
            [
                'nombre' => $this->nombre,
                'fase_id' => $this->fase_id,
                'descripcion' => $this->descripcion
            ]
        );

        // image

        $this->noty($this->selected_id < 1 ? 'Estado Procesal Registrado' : 'Estado Procesal Actualizado', 'noty', false, 'close-modal');
        $this->resetUI();
    }

    public function Destroy(EstadoProcesal $estado)
    {
        //dd($estado);    
        $estado->delete();
        $this->noty('Se eliminó el estado procesal');
    }
}

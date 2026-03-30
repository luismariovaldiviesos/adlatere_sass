<?php

namespace App\Http\Livewire;

use Livewire\Component;
use App\Models\Especialidad;
use App\Models\Materia;
use Livewire\WithPagination;
use Livewire\WithFileUploads;

class Especialidades extends Component
{
     use WithPagination;
    public $nombre = '', $materia_id="", $materia="", $observaciones = "", $selected_id = 0;
    public $action = 'Listado', $componentName = 'Listado de Especialidades', $search, $form = false;
    private $pagination = 10;
    protected $paginationTheme = 'tailwind';

    

    public function render(){
        if (strlen($this->search) > 0){
            $searchTerm = '%' . trim($this->search) . '%';
            $info =  Especialidad::where('nombre', 'like', $searchTerm)
            ->withCount('materia')->paginate($this->pagination);
        }           
        else
            $info = Especialidad::withCount('materia')->paginate($this->pagination);
            $materias = Materia::all();
        return view('livewire.especialidades.component', ['especialidades' => $info, 'materias' => $materias])
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
        $this->reset('nombre', 'materia_id', 'materia', 'observaciones', 'selected_id', 'search', 'action', 'componentName', 'form');
    }

    public function Edit(Especialidad $especialidad)
    {
        $this->selected_id = $especialidad->id;
        $this->nombre = $especialidad->nombre;
        $this->materia_id = $especialidad->materia_id;
        $this->observaciones = $especialidad->observaciones;
        $this->action = 'Editar';
        $this->form = true;

    }

    public function Store()
    {
        sleep(1);

        $this->validate(Especialidad::rules($this->selected_id), Especialidad::$messages);

        $especialidad = Especialidad::updateOrCreate(
            ['id' => $this->selected_id],
            ['nombre' => $this->nombre, 'materia_id' => $this->materia_id, 'observaciones' => $this->observaciones]
        );

        // image

        $this->noty($this->selected_id < 1 ? 'Especialidad Registrada' : 'Especialidad Actualizada', 'noty', false, 'close-modal');
        $this->resetUI();
    }

    public function Destroy(Especialidad $especialidad)
    {
        $especialidad->delete();
        $this->noty('Se eliminó la Especialidad');
    }
}

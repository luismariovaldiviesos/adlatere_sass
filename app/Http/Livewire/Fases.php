<?php

namespace App\Http\Livewire;

use Livewire\Component;
use App\Models\Fase;
use Livewire\WithPagination;
use Livewire\WithFileUploads;

class Fases extends Component
{
     use WithPagination;
    public $nombre = '', $selected_id = 0;
    public $action = 'Listado', $componentName = 'Listado de Fases', $search, $form = false;
    private $pagination = 10;
    protected $paginationTheme = 'tailwind';

    

    public function render(){
        if (strlen($this->search) > 0){
            $searchTerm = '%' . trim($this->search) . '%';
            $info =  Fase::where('nombre', 'like', $searchTerm)
            ->withCount('estados_procesales')->paginate($this->pagination);
        }           
        else
            $info = Fase::withCount('estados_procesales')->paginate($this->pagination);
        return view('livewire.fases.component', ['fases' => $info])
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
        $this->reset('nombre', 'selected_id', 'search', 'action', 'componentName', 'form');
    }

    public function Edit(Fase $fase)
    {
        $this->selected_id = $fase->id;
        $this->nombre = $fase->nombre;
        $this->action = 'Editar';
        $this->form = true;

    }

    public function Store()
    {
        sleep(1);

        $this->validate(Fase::rules($this->selected_id), Fase::$messages);

        $fase = Fase::updateOrCreate(
            ['id' => $this->selected_id],
            ['nombre' => $this->nombre]
        );

        // image

        $this->noty($this->selected_id < 1 ? 'Fase Registrada' : 'Fase Actualizada', 'noty', false, 'close-modal');
        $this->resetUI();
    }

    public function Destroy(Fase $fase)
    {
        $fase->delete();
        $this->noty('Se eliminó la Fase');
    }
}

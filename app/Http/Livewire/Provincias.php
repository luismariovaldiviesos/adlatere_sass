<?php

namespace App\Http\Livewire;

use App\Models\Provincia;
use Livewire\Component;
use Livewire\WithPagination;

class Provincias extends Component
{

    use WithPagination;


    public $nombre = '', $selected_id = 0;
    public $action = 'Listado', $componentName = 'Listado de Provincias', $search, $form = false;
    private $pagination = 10;
    protected $paginationTheme = 'tailwind';

    public function render()
    {
        if (strlen($this->search) > 0)
        $info = Provincia::where('nombre', 'like', "%{$this->search}%")->paginate($this->pagination);
    else
        $info = Provincia::paginate($this->pagination);


    return view('livewire.provincias.component', ['provincias' => $info])
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

    public function Edit(Provincia $provincia)
    {
        $this->selected_id = $provincia->id;
        $this->nombre = $provincia->nombre;
        $this->action = 'Editar';
        $this->form = true;

    }

    public function Store()
    {
        sleep(1);

        $this->validate(Provincia::rules($this->selected_id), Provincia::$messages);

        $provincia = Provincia::updateOrCreate(
            ['id' => $this->selected_id],
            ['nombre' => $this->nombre]
        );

        // image

        $this->noty($this->selected_id < 1 ? 'Provincia Registrada' : 'Provincia Actualizada', 'noty', false, 'close-modal');
        $this->resetUI();
    }

    public function Destroy(Provincia $provincia)
    {
        $provincia->delete();
        $this->noty('Se eliminó la Provincia');
    }
}

<?php

namespace App\Http\Livewire;

use App\Models\Canton;
use App\Models\Provincia;
use Livewire\Component;
use Livewire\WithPagination;

class Cantones extends Component
{
    use WithPagination;

    public $nombre = '', $provincia_id, $provincia,  $selected_id = 0;
    public $action = 'Listado', $componentName = 'Listado de cantones', $search, $form = false;
    private $pagination = 10;
    protected $paginationTheme = 'tailwind';

    public function render()
    {
        $query = Canton::join('provincias as p', 'p.id', 'cantones.provincia_id')
            ->select('cantones.*', 'p.nombre as provincia')
            // Suponiendo que la relación en el modelo Canton se llama 'unidades'
            ->withCount('unidades') 
            ->orderBy('cantones.nombre', 'asc');

        if (strlen($this->search) > 0) {
            $searchTerm = "%{$this->search}%";
            $query->where(function($q) use ($searchTerm) {
                $q->where('cantones.nombre', 'like', $searchTerm)
                ->orWhere('p.nombre', 'like', $searchTerm);
            });
        }

        $info = $query->paginate($this->pagination);

        return view('livewire.cantones.component', [
            'cantones' => $info,
            'provincias' => Provincia::orderBy('nombre', 'asc')->get(),
        ])->layout('layouts.theme.app');
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
        $this->reset('nombre', 'provincia_id', 'selected_id', 'search', 'action', 'componentName', 'form','provincia');
    }

    public function Edit(Canton $canton)
    {
        $this->selected_id = $canton->id;
        $this->nombre = $canton->nombre;
        $this->provincia_id =  $canton->provincia_id;
        $this->action = 'Editar';
        $this->form = true;

    }

    public function Store()
    {
        sleep(1);

        $this->validate(Canton::rules($this->selected_id), Canton::$messages);

        $canton = Canton::updateOrCreate(
            ['id' => $this->selected_id],
            [
                'nombre' => $this->nombre,
                'provincia_id' => $this->provincia_id
            ]
        );

        // image

        $this->noty($this->selected_id < 1 ? 'Cantón Registrado' : 'Cantón Actualizado', 'noty', false, 'close-modal');
        $this->resetUI();
    }

    public function Destroy(Canton $canton)
    {
        $canton->delete();
        $this->noty('Se eliminó el cantón');
    }

}

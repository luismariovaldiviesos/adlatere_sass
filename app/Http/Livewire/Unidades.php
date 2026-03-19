<?php

namespace App\Http\Livewire;

use App\Models\Canton;
use App\Models\Unidad;
use Livewire\Component;
use Livewire\WithPagination;

class Unidades extends Component
{

    use WithPagination;

    public $nombre = '', $canton_id, $canton,  $selected_id = 0, $unidad="";
    public $action = 'Listado', $componentName = 'Listado de Unidades Judicial', $search, $form = false;
    private $pagination = 20;
    protected $paginationTheme = 'tailwind';

    public function render()
    {
        if(strlen($this->search) > 0)  

        $info =  Unidad::join('cantones as c','c.id','unidads.canton_id')
                ->select('unidads.*','c.nombre as canton')
                ->where(function ($query){
                    $searchTerm =  strtolower($this->search);
                    $query->whereRaw('LOWER(unidads.nombre) LIKE ?', ["%{$searchTerm}%"])
          ->orWhereRaw('LOWER(c.nombre) LIKE ?', ["%{$searchTerm}%"]);
                })->paginate($this->pagination);
        else

        $info =  Unidad::join('cantones as c','c.id','unidads.canton_id')
        ->select('unidads.*','c.nombre as canton')
         ->paginate($this->pagination);


        return view('livewire.unidades.component', [
        'unidades' => $info,
        'cantones' => Canton::orderBy('id','asc')->get(),
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
        $this->reset('nombre', 'canton_id', 'selected_id', 'search', 'action', 'componentName', 'form','unidad');
    }

    public function Edit(Unidad $unidad)
    {
        $this->selected_id = $unidad->id;
        $this->nombre = $unidad->nombre;
        $this->canton_id =  $unidad->canton_id;
        $this->action = 'Editar';
        $this->form = true;

    }
    public function Store()
    {
        sleep(1);

        $this->validate(Unidad::rules($this->selected_id), Unidad::$messages);

        $unidad = Unidad::updateOrCreate(
            ['id' => $this->selected_id],
            [
                'nombre' => $this->nombre,
                'canton_id' => $this->canton_id
            ]
        );

        // image

        $this->noty($this->selected_id < 1 ? 'Unidad Registrada' : 'UNidad Actualizada', 'noty', false, 'close-modal');
        $this->resetUI();
    }

    public function Destroy(Unidad $unidad)
    {
        $unidad->delete();
        $this->noty('Se eliminó la unidad');
    }

}

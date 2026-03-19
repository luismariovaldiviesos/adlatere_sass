<?php

namespace App\Http\Livewire;

use App\Models\Materia;
use App\Models\Unidad;
use Livewire\Component;
use Livewire\WithPagination;


class Materias extends Component
{

use WithPagination;

    public $nombre = '', $unidad_id, $unidad,  $selected_id = 0, $materia="";
    public $action = 'Listado', $componentName = 'Listado de Materias ', $search, $form = false;
    private $pagination = 20;
    protected $paginationTheme = 'tailwind';
    
    // public function mount(){
    //     dd($this->materia, $this->nombre, $this->unidad_id, $this->unidad, $this->selected_id, $this->action, $this->componentName, $this->search, $this->form  );
    // }

      public function render()
    {
        
        if(strlen($this->search) > 0)  
           
        $info =  Materia::join('unidads as u','u.id','materias.unidad_id')
                ->select('materias.*','u.nombre as unidad')
                ->where(function ($query){
                    $searchTerm =  strtolower($this->search);
                    $query->whereRaw('LOWER(materias.nombre) LIKE ?', ["%{$searchTerm}%"])
          ->orWhereRaw('LOWER(u.nombre) LIKE ?', ["%{$searchTerm}%"]);
                })->paginate($this->pagination);
        else

        $info =  Materia::join('unidads as u','u.id','materias.unidad_id')
        ->select('materias.*','u.nombre as unidad')
         ->paginate($this->pagination);


        return view('livewire.materias.component', [
        'materias' => $info,
        'unidades' => Unidad::orderBy('id','asc')->get(),
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
        $this->reset('nombre', 'unidad_id', 'selected_id', 'search', 'action', 'componentName', 'form','materia');
    }

       public function Edit(Materia $materia)
    {
        $this->selected_id = $materia->id;
        $this->nombre = $materia->nombre;
        $this->unidad_id =  $materia->unidad_id;
        $this->action = 'Editar';
        $this->form = true;

    }

     public function Store()
    {
        sleep(1);

        $this->validate(Materia::rules($this->selected_id), Materia::$messages);

        $materia = Materia::updateOrCreate(
            ['id' => $this->selected_id],
            [
                'nombre' => $this->nombre,
                'unidad_id' => $this->unidad_id
            ]
        );

        // image

        $this->noty($this->selected_id < 1 ? 'Materia Registrada' : 'Materia Actualizada', 'noty', false, 'close-modal');
        $this->resetUI();
    }

    public function Destroy(Materia $materia)
    {
        $materia->delete();
        $this->noty('Materia Eliminada');

    }
}

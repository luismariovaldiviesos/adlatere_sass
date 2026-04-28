<?php

namespace App\Http\Livewire;

use Livewire\Component;
use App\Models\TipoActividad;
use Livewire\WithPagination;
use Livewire\WithFileUploads;

class TiposActividades extends Component
{
    use WithPagination;
    use WithFileUploads;

  public $nombre = '',  $selected_id = 0;
  //public $tipos =  '';
    public $action = 'Listado', $componentName = 'Listado de Tipos de Actividades', $search, $form = false;
    private $pagination = 10;
    protected $paginationTheme = 'tailwind';

    public function render()
    {
        if (strlen($this->search) > 0)
            $info = TipoActividad::where('nombre', 'like', "%{$this->search}%")->paginate($this->pagination);
        else
            $info = TipoActividad::paginate($this->pagination);
        return view('livewire.tipos_actividades.component', ['tipos' => $info])
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
        $this->contenido = '';
        //por si acaso emitimos para limpiar el editor de texto enriquecido
        $this->dispatchBrowserEvent('set-editor-content',['content' => '']);
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

    public function Edit(TipoActividad $tipo)
    {
        $this->selected_id = $tipo->id;
        $this->nombre = $tipo->nombre;
        //$this->descripcion = $tipo->descripcion;
        $this->action = 'Editar';
        $this->form = true;
    }

    public function Store(){
          $this->validate(TipoActividad::rules($this->selected_id), TipoActividad::$messages);
        TipoActividad::updateOrCreate(['id' => $this->selected_id], [
            'nombre' => $this->nombre,
         ]);
         $this->noty($this->selected_id < 1 ? 'Tipo de Actividad Registrado' : 'Tipo de Actividad Actualizado', 'noty', false, 'close-modal');
        $this->resetUI();
    }

      public function Destroy(TipoActividad $tipo)
    {
        //dd($tipo);
        $tipo->delete();
        $this->noty('Se eliminó el Tipo de Actividad');
    }
}

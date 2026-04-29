<?php

namespace App\Http\Livewire;

use Livewire\Component;
use App\Models\PlantillaTipoActividad;
use App\Models\TipoActividad;
use Livewire\WithPagination;

class PlantillasTiposActividad extends Component
{

use WithPagination;
    public $nombre = '', $tipo_actividad_id, $contenido = '', $activo = 1, $selected_id = 0;
    public $action = 'Listado', $componentName = 'Plantillas de Actividad', $search, $form = false;
    public $previewing = false, $preview_content = '', $preview_title = '';
    private $pagination = 20;
    protected $paginationTheme = 'tailwind';

    public function render()
    {
        $plantillas = PlantillaTipoActividad::with('tipoActividad')
            ->where('nombre', 'like', '%' . $this->search . '%')
            ->orderBy('id', 'desc')
            ->paginate($this->pagination);

        return view('livewire.plantillas_tipos_actividades.component', [
            'plantillas' => $plantillas,
            'tipos' => TipoActividad::orderBy('nombre', 'asc')->get()
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

    public function Preview($id)
    {
        $plantilla = PlantillaTipoActividad::find($id);
        if ($plantilla) {
            $this->preview_title = $plantilla->nombre;
            $this->preview_content = $plantilla->contenido;
            $this->dispatchBrowserEvent('show-preview-modal');
        }
    }

    public function closePreview()
    {
        $this->preview_content = '';
        $this->preview_title = '';
    }

    public function resetUI()
    {
        // limpiar mensajes rojos de validación
        $this->resetValidation();
        // regresar a la página inicial del componente
        $this->resetPage();
        // regresar propiedades a su valor por defecto
        $this->reset('nombre', 'selected_id', 'search', 'action', 'form', 'contenido', 'tipo_actividad_id');
    }

    public function Edit(PlantillaTipoActividad $plantilla)
    {
        $this->selected_id = $plantilla->id;
        $this->nombre = $plantilla->nombre;
        $this->tipo_actividad_id =  $plantilla->tipo_actividad_id;
        $this->contenido =  $plantilla->contenido;
        $this->activo =  $plantilla->activo;
        $this->action = 'Editar';
        $this->form = true;
        $this->dispatchBrowserEvent('set-editor-content', ['content' => $this->contenido]);
    }
    public function Store (){
        $this->validate(PlantillaTipoActividad::rules($this->selected_id), PlantillaTipoActividad::$messages);
        PlantillaTipoActividad::updateOrCreate(['id' => $this->selected_id], [
            'nombre' => $this->nombre,
            'tipo_actividad_id' => $this->tipo_actividad_id,
            'contenido' => $this->contenido,
            'activo' => $this->activo,
         ]);
         $this->noty($this->selected_id > 0 ? 'Plantilla de Actividad Actualizada' : 'Plantilla de Actividad Creada');
         $this->resetUI();
    }

    public function Destroy(PlantillaTipoActividad $plantilla)
    {
        if ($plantilla->tipoActividad()->count() > 0) {
            $this->noty('No se puede eliminar la Plantilla de Actividad porque tiene actividades asociadas', 'noty', false);
            return;
        }
        $plantilla->delete();
        $this->noty('Plantilla de Actividad Eliminada');
    }
}

<?php

namespace App\Http\Livewire;

use Livewire\Component;
use App\Models\Fase;
use App\Models\Juicio;
use App\Models\Materia;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use Illuminate\Validation\ValidationException;

class Fases extends Component
{
     use WithPagination;
    public $nombre = '', $materia_id, $selected_id = 0;
    public $action = 'Listado', $componentName = 'Listado de Fases', $search, $form = false;
    private $pagination = 10;
    protected $paginationTheme = 'tailwind';

    

    public function render(){
        if (strlen((string) $this->search) > 0){
            $searchTerm = '%' . trim($this->search) . '%';
            $info =  Fase::where('nombre', 'like', $searchTerm)
            ->with('materia')
            ->withCount('estados_procesales')->paginate($this->pagination);
        }           
        else
            $info = Fase::with('materia')->withCount('estados_procesales')->paginate($this->pagination);
        return view('livewire.fases.component', [
            'fases' => $info,
            'materias' => Materia::orderBy('nombre', 'asc')->get(),
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
        $this->reset('nombre', 'materia_id', 'selected_id', 'search', 'action', 'componentName', 'form');
    }

    public function Edit(Fase $fase)
    {
        $this->selected_id = $fase->id;
        $this->nombre = $fase->nombre;
        $this->materia_id = $fase->materia_id;
        $this->action = 'Editar';
        $this->form = true;

    }

    public function Store()
    {
        try {
            $this->validate(Fase::rules($this->selected_id), Fase::$messages);
        } catch (ValidationException $e) {
            $this->dispatchBrowserEvent('noty', [
                'msg' => 'No se guardó la fase. Revise los campos marcados.',
                'type' => 'error',
                'action' => 'error',
            ]);

            throw $e;
        }

        if ($this->selected_id > 0) {
            $faseActual = Fase::find($this->selected_id);

            if ($faseActual && (int) $faseActual->materia_id !== (int) $this->materia_id) {
                $enUsoOtraMateria = Juicio::whereHas('estadoProcesal', fn ($q) => $q->where('fase_id', $faseActual->id))
                    ->whereHas('asunto.procedimiento', fn ($q) => $q->where('materia_id', '!=', $this->materia_id))
                    ->exists();

                if ($enUsoOtraMateria) {
                    $this->noty('No puede cambiar la materia: esta fase tiene expedientes asociados a otra materia.', 'noty', false, 'error');
                    return;
                }
            }
        }

        $fase = Fase::updateOrCreate(
            ['id' => $this->selected_id],
            ['nombre' => $this->nombre, 'materia_id' => $this->materia_id]
        );

        // image

        $this->noty($this->selected_id < 1 ? 'Fase Registrada' : 'Fase Actualizada', 'noty', false, 'close-modal');
        $this->resetUI();
    }

    public function Destroy(Fase $fase)
    {
        if (\App\Models\EstadoProcesal::where('fase_id', $fase->id)->exists()) {
            $this->noty('No se puede eliminar la fase porque tiene estados procesales asociados.', 'noty', false, 'error');
            return;
        }

        $fase->delete();
        $this->noty('Se eliminó la fase');
    }
}

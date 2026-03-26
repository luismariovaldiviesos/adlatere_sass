<?php

namespace App\Http\Livewire;

use App\Models\Procedimiento;
use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Asunto;

class Asuntos extends Component
{
    use WithPagination;

     public $nombre = '', $procedimiento_id, $asunto,  $selected_id = 0, $procedimiento="";
    public $action = 'Listado', $componentName = 'Listado de Asuntos ', $search, $form = false;
    private $pagination = 20;
    protected $paginationTheme = 'tailwind';

   public function render()
    {
        // 1. Consulta Base con Joins
        $query = Asunto::join('procedimientos as p', 'p.id', 'asuntos.procedimiento_id')
            ->join('materias as m', 'm.id', 'p.materia_id')
            ->select(
                'asuntos.id', 
                'asuntos.nombre as nombre_asunto', // Alias único para evitar choques
                'p.nombre as nombre_procedimiento', 
                'm.nombre as nombre_materia'
            );

        // 2. Filtro de búsqueda (Asegúrate que $this->search no tenga espacios locos)
        if (trim($this->search) !== '') {
            $searchTerm = '%' . strtolower(trim($this->search)) . '%';
            
            $query->where(function ($q) use ($searchTerm) {
                $q->whereRaw('LOWER(asuntos.nombre) LIKE ?', [$searchTerm])
                ->orWhereRaw('LOWER(p.nombre) LIKE ?', [$searchTerm])
                ->orWhereRaw('LOWER(m.nombre) LIKE ?', [$searchTerm]);
            });
        }

        $info = $query->orderBy('asuntos.id', 'asc')
                    // ->orderBy('m.id', 'asc')
                    // ->orderBy('p.id', 'asc')
                    ->paginate($this->pagination);

        return view('livewire.asuntos.component', [
            'asuntos' => $info,
            'procedimientos' => Procedimiento::with('materia')->get(),
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
        $this->reset('nombre', 'procedimiento_id', 'selected_id', 'search', 'action', 'componentName', 'form','asunto');
    }

       public function Edit(Asunto $asunto)
    {
        $this->selected_id = $asunto->id;
        $this->nombre = $asunto->nombre;
        $this->procedimiento_id =  $asunto->procedimiento_id;
        $this->action = 'Editar';
        $this->form = true;

    }

    public function Store()
    {
        sleep(1);

        $this->validate(Asunto::rules($this->selected_id, $this->procedimiento_id), Asunto::$messages);

        $asunto = Asunto::updateOrCreate(
            ['id' => $this->selected_id],
            [
                'nombre' => $this->nombre,
                'procedimiento_id' => $this->procedimiento_id
            ]
        );

        // image

        $this->noty($this->selected_id < 1 ? 'Asunto Registrado' : 'Asunto Actualizado', 'noty', false, 'close-modal');
        $this->resetUI();
    }

     public function Destroy(Asunto $asunto)
    {
        //dd($asunto);
        $asunto->delete();
        $this->noty('Asunto Eliminado');

    }



}

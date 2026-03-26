<?php

namespace App\Http\Livewire;

use App\Models\Materia;
use App\Models\Procedimiento;
use Livewire\Component;
use Livewire\WithPagination;

class Procedimientos extends Component
{
    use WithPagination;

     public $nombre = '', $materia_id, $procedimiento,  $selected_id = 0, $materia="";
    public $action = 'Listado', $componentName = 'Listado de Procedimientos ', $search, $form = false;
    private $pagination = 20;
    protected $paginationTheme = 'tailwind';

    public function render()
    {
        // 1. Consulta base con Join y conteo de expedientes vinculados
        $query = Procedimiento::join('materias as m', 'm.id', 'procedimientos.materia_id')
            ->select('procedimientos.*', 'm.nombre as materia')
            ->withCount('asuntos'); // Asumiendo que la relación en el modelo es 'procesos'
            //->orderBy('procedimientos.nombre', 'asc');

        // 2. Filtro de búsqueda optimizado
        if (strlen($this->search) > 0) {
            $searchTerm = strtolower(trim($this->search));
            $query->where(function ($q) use ($searchTerm) {
                $q->whereRaw('LOWER(procedimientos.nombre) LIKE ?', ["%{$searchTerm}%"])
                ->orWhereRaw('LOWER(m.nombre) LIKE ?', ["%{$searchTerm}%"]);
            });
        }

        // 3. Paginación y Retorno
        $info = $query->paginate($this->pagination);

        return view('livewire.procedimientos.component', [
            'procedimientos' => $info,
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
        $this->reset('nombre', 'materia_id', 'selected_id', 'search', 'action', 'componentName', 'form','procedimiento');
    }

       public function Edit(Procedimiento $procedimiento)
    {
        $this->selected_id = $procedimiento->id;
        $this->nombre = $procedimiento->nombre;
        $this->materia_id =  $procedimiento->materia_id;
        $this->action = 'Editar';
        $this->form = true;

    }

    public function Store()
    {
        sleep(1);

        $this->validate(Procedimiento::rules($this->selected_id, $this->materia_id), Procedimiento::$messages);

        $procedimiento = Procedimiento::updateOrCreate(
            ['id' => $this->selected_id],
            [
                'nombre' => $this->nombre,
                'materia_id' => $this->materia_id
            ]
        );

        // image

        $this->noty($this->selected_id < 1 ? 'Procedimiento Registrado' : 'Procedimiento Actualizado', 'noty', false, 'close-modal');
        $this->resetUI();
    }

     public function Destroy(Procedimiento $procedimiento)
    {
        $procedimiento->delete();
        $this->noty('Procedimiento Eliminado');

    }
}

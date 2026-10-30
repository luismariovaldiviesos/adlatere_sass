<?php

namespace App\Http\Livewire;

use App\Models\Materia;
use Livewire\Component;
use Livewire\WithPagination;


class Materias extends Component
{

use WithPagination;

    public $nombre = '', $selected_id = 0, $materia="";
    public $action = 'Listado', $componentName = 'Listado de Materias ', $search, $form = false;
    private $pagination = 20;
    protected $paginationTheme = 'tailwind';
    
  

    public function render()
    {
        // 1. Iniciamos la consulta base con el conteo de procedimientos
        $query = Materia::withCount('procedimientos');
            // ->orderBy('materias.nombre', 'asc');

        // 2. Aplicamos el filtro de búsqueda solo si hay texto
        if (strlen($this->search) > 0) {
            $searchTerm = strtolower(trim($this->search));
            $query->where(function ($q) use ($searchTerm) {
                $q->whereRaw('LOWER(materias.nombre) LIKE ?', ["%{$searchTerm}%"]);
            });
        }

        // 3. Ejecutamos la paginación
        $info = $query->paginate($this->pagination);

        return view('livewire.materias.component', [
            'materias' => $info,
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
        $this->reset('nombre', 'selected_id', 'search', 'action', 'componentName', 'form','materia');
    }

       public function Edit(Materia $materia)
    {
        $this->selected_id = $materia->id;
        $this->nombre = $materia->nombre;
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
            ]
        );

        // image

        $this->noty($this->selected_id < 1 ? 'Materia Registrada' : 'Materia Actualizada', 'noty', false, 'close-modal');
        $this->resetUI();
    }

    public function Destroy(Materia $materia)
    {
        $tieneProcedimientos = \App\Models\Procedimiento::where('materia_id', $materia->id)->exists();
        $tieneEspecialidades = \App\Models\Especialidad::where('materia_id', $materia->id)->exists();

        if ($tieneProcedimientos || $tieneEspecialidades) {
            $this->noty('No se puede eliminar la materia porque tiene procedimientos o especialidades asociadas.', 'noty', false, 'error');
            return;
        }

        $materia->delete();
        $this->noty('Materia eliminada');
    }
}

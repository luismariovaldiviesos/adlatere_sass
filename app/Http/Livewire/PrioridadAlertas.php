<?php

namespace App\Http\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\PrioridadAlerta;

class PrioridadAlertas extends Component
{
    use WithPagination;

    public $prioridad = '', $dias_verde = 5, $dias_amarillo = 15, $dias_rojo = 25;
    public $mensaje = '', $activo = true, $selected_id = 0;
    public $action = 'Listado', $componentName = 'Alertas por prioridad', $search, $form = false;
    private $pagination = 20;
    protected $paginationTheme = 'tailwind';

    public function render()
    {
        $query = PrioridadAlerta::orderBy('id', 'asc');

        if (strlen($this->search) > 0) {
            $searchTerm = strtolower(trim($this->search));
            $query->where(function ($q) use ($searchTerm) {
                $q->whereRaw('LOWER(prioridad) LIKE ?', ["%{$searchTerm}%"])
                  ->orWhereRaw('LOWER(mensaje) LIKE ?', ["%{$searchTerm}%"]);
            });
        }

        $info = $query->paginate($this->pagination);

        return view('livewire.prioridad-alertas.component', [
            'alertas' => $info,
        ])->layout('layouts.theme.app');
    }

    public $listeners = [
        'resetUI',
        'Destroy'
    ];

    public function updatedForm()
    {
        if ($this->selected_id > 0)
            $this->action = 'Editar';
        else
            $this->action = 'Agregar';
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
        $this->resetValidation();
        $this->resetPage();
        $this->reset('prioridad', 'dias_verde', 'dias_amarillo', 'dias_rojo', 'mensaje', 'activo', 'selected_id', 'search', 'action', 'componentName', 'form');
        $this->dias_verde = 5;
        $this->dias_amarillo = 15;
        $this->dias_rojo = 25;
        $this->activo = true;
    }

    public function Edit(PrioridadAlerta $alerta)
    {
        $this->selected_id = $alerta->id;
        $this->prioridad = $alerta->prioridad;
        $this->dias_verde = $alerta->dias_verde;
        $this->dias_amarillo = $alerta->dias_amarillo;
        $this->dias_rojo = $alerta->dias_rojo;
        $this->mensaje = $alerta->mensaje;
        $this->activo = (bool) $alerta->activo;
        $this->action = 'Editar';
        $this->form = true;
    }

    public function Store()
    {
        sleep(1);

        $reglas = [
            'prioridad' => 'required|string|max:20' . ($this->selected_id > 0 ? '' : '|unique:prioridad_alertas,prioridad'),
            'dias_verde' => 'required|integer|min:0',
            'dias_amarillo' => 'required|integer|min:0',
            'dias_rojo' => 'required|integer|min:0',
            'mensaje' => 'nullable|string|max:255',
        ];
        $this->validate($reglas, [
            'prioridad.required' => 'Indique el nombre de la prioridad.',
            'prioridad.unique' => 'Esa prioridad ya existe.',
            'dias_verde.required' => 'Indique los días en verde.',
            'dias_amarillo.required' => 'Indique los días en amarillo.',
            'dias_rojo.required' => 'Indique los días en rojo.',
        ]);

        if (!($this->dias_verde < $this->dias_amarillo && $this->dias_amarillo <= $this->dias_rojo)) {
            $this->addError('dias_amarillo', 'Debe cumplirse: verde < amarillo <= rojo.');
            return;
        }

        PrioridadAlerta::updateOrCreate(
            ['id' => $this->selected_id],
            [
                'prioridad' => $this->prioridad,
                'dias_verde' => (int) $this->dias_verde,
                'dias_amarillo' => (int) $this->dias_amarillo,
                'dias_rojo' => (int) $this->dias_rojo,
                'mensaje' => $this->mensaje ?: null,
                'activo' => (bool) $this->activo,
            ]
        );

        $this->noty($this->selected_id < 1 ? 'Alerta registrada' : 'Alerta actualizada', 'noty', false, 'close-modal');
        $this->resetUI();
    }

    public function Destroy(PrioridadAlerta $alerta)
    {
        $alerta->delete();
        $this->noty('Alerta eliminada');
    }
}

<?php

namespace App\Http\Livewire;


use Livewire\Component;
use App\Models\Funcionario;
use Livewire\WithPagination;



class Funcionarios extends Component
{
    use WithPagination;
    


    public $nombre = '', $selected_id = 0, $cargo = '', $telefono = '', $email = '';
    public $action = 'Listado', $componentName = 'Funcionarios', $search, $form = false;
    private $pagination = 10;
    protected $paginationTheme = 'tailwind';



    public function render()
    {
        if (strlen($this->search) > 0)
            $info = Funcionario::where('nombre', 'like', "%{$this->search}%")->paginate($this->pagination);
        else
            $info = Funcionario::paginate($this->pagination);


        return view('livewire.funcionarios.component', ['funcionarios' => $info])
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
        $this->reset('nombre', 'selected_id', 'search', 'action', 'componentName', 'cargo', 'telefono', 'email', 'form');
    }

    public function Edit(Funcionario $funcionario)
    {
        $this->selected_id = $funcionario->id;
        $this->nombre = $funcionario->nombre;
        $this->cargo = $funcionario->cargo;
        $this->telefono = $funcionario->telefono;
        $this->email = $funcionario->email;
        $this->action = 'Editar';
        $this->form = true;

    }

    public function Store()
    {
        sleep(1);

        $this->validate(Funcionario::rules($this->selected_id), Funcionario::$messages);

        $funcionario = Funcionario::updateOrCreate(
            ['id' => $this->selected_id],
            ['nombre' => $this->nombre, 'cargo' => $this->cargo, 'telefono' => $this->telefono, 'email' => $this->email]
        );

        
        
        $this->noty($this->selected_id < 1 ? 'Funcionario Registrado' : 'Funcionario Actualizado', 'noty', false, 'close-modal');
        $this->resetUI();
    }


    public function Destroy(Funcionario $funcionario)
    {
        $funcionario->delete();
        $this->noty('Se eliminó el Funcionario');
    }
}

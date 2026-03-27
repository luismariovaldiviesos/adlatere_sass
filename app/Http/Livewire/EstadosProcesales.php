<?php

namespace App\Http\Livewire;


use Livewire\Component;
use App\Models\EstadoProcesal;
use Livewire\WithPagination;
use Livewire\WithFileUploads;

class EstadosProcesales extends Component

{
    use WithPagination;
    use WithFileUploads;


    public $name = '', $selected_id = 0, $photo = '';
    public $action = 'Listado', $componentName = 'Estados Procesales', $search, $form = false, $fase_id;
    private $pagination = 10;
    protected $paginationTheme = 'tailwind';

     public function render()
    {
        if (strlen($this->search) > 0)
            $info = EstadoProcesal::where('name', 'like', "%{$this->search}%")->paginate($this->pagination);
        else
            $info = EstadoProcesal::paginate($this->pagination);


        return view('livewire.estados_procesales.component', ['estados' => $info])
            ->layout('layouts.theme.app');
    }
}

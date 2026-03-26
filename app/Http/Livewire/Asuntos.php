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

    $info = $query->orderBy('m.nombre', 'asc')
                  ->orderBy('p.nombre', 'asc')
                  ->paginate($this->pagination);

    return view('livewire.asuntos.component', [
        'asuntos' => $info,
        'procedimientos' => Procedimiento::with('materia')->get(),
    ])->layout('layouts.theme.app');
}
}

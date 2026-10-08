<?php

namespace App\Http\Livewire;

use Livewire\Component;
use App\Models\Audiencia;

class Calendario extends Component
{
    public $eventos = [];
    public $filtroEstado = 'todas'; // todas|Programada|Realizada|Suspendida

    public function mount()
    {
        $this->cargarEventos();
    }

    public function updatedFiltroEstado()
    {
        $this->cargarEventos();
        $this->dispatchBrowserEvent('calendario-actualizar', ['eventos' => $this->eventos]);
    }

    private function cargarEventos()
    {
        $q = Audiencia::with([
                'juicio.asunto.procedimiento.materia',
                'juicio.estadoProcesal',
                'juicio.abogados',
                'juicio.actores',
                'juicio.demandados',
            ])
            ->where('fecha_hora', '>=', now()->copy()->subDays(90)->startOfDay())
            ->where('fecha_hora', '<=', now()->copy()->addYear()->endOfDay())
            ->orderBy('fecha_hora', 'asc');

        // Visibilidad: las audiencias agendadas las ven todos los usuarios
        if ($this->filtroEstado !== 'todas') {
            $q->where('estado', $this->filtroEstado);
        }

        $colores = [
            'Programada' => '#3b82f6',
            'Realizada'  => '#22c55e',
            'Suspendida' => '#eab308',
        ];

        $this->eventos = $q->get()->map(function ($a) use ($colores) {
            $j = $a->juicio;
            $patrocinadores = $j ? $j->abogados->filter(function ($ab) {
                $rol = strtolower(trim($ab->pivot->rol_en_juicio ?? ''));
                return str_contains($rol, 'abogado') && str_contains($rol, 'patrocinador');
            })->pluck('name')->implode(', ') : '';
            return [
                'title' => ($j->cod_satje ?? 'S/N') . ' · ' . ($a->tipo_audiencia ?? 'Audiencia'),
                'start' => \Carbon\Carbon::parse($a->fecha_hora)->toIso8601String(),
                'color' => $colores[$a->estado] ?? '#6b7280',
                'extendedProps' => [
                    'estado' => $a->estado,
                    'tipo' => $a->tipo_audiencia,
                    'sala' => $a->sala_enlace,
                    'juicio' => $j->cod_satje ?? '—',
                    'asunto' => $j->asunto->nombre ?? '—',
                    'materia' => $j->asunto->procedimiento->materia->nombre ?? '—',
                    'estadoProcesal' => $j->estadoProcesal->nombre ?? '—',
                    'patrocinadores' => $patrocinadores !== '' ? $patrocinadores : 'Sin asignar',
                    'actores' => $j ? ($j->actores->pluck('businame')->implode(', ') ?: '—') : '—',
                    'demandados' => $j ? ($j->demandados->pluck('businame')->implode(', ') ?: '—') : '—',
                    'clientes' => $j ? (function () use ($j) {
                        // Arreglos planos (no merge de colecciones Eloquent con strings)
                        $marcados = [];
                        foreach (['actores' => 'Actor', 'demandados' => 'Demandado'] as $rel => $lado) {
                            foreach ($j->$rel as $p) {
                                if (!empty($p->pivot->es_cliente)) {
                                    $marcados[] = $p->businame . ' (' . $lado . ')';
                                }
                            }
                        }
                        if (!empty($marcados)) {
                            return implode(', ', $marcados);
                        }
                        $todos = [];
                        foreach (['actores' => 'Actor', 'demandados' => 'Demandado'] as $rel => $lado) {
                            foreach ($j->$rel as $p) {
                                $todos[] = $p->businame . ' (' . $lado . ')';
                            }
                        }
                        return $todos ? implode(', ', $todos) : '—';
                    })() : '—',
                    'hora' => \Carbon\Carbon::parse($a->fecha_hora)->format('H:i'),
                    'fecha' => \Carbon\Carbon::parse($a->fecha_hora)->format('d/m/Y H:i'),
                ],
            ];
        })->toArray();
    }

    public function render()
    {
        return view('livewire.calendario.component')->layout('layouts.theme.app');
    }
}

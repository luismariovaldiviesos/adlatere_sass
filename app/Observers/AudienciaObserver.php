<?php

namespace App\Observers;

use App\Models\Audiencia;
use App\Services\Notificador;
use Illuminate\Support\Facades\DB;

class AudienciaObserver
{
    private function abogadosDelJuicio(int $juicioId): array
    {
        return DB::table('juicio_user')
            ->where('juicio_id', $juicioId)
            ->pluck('user_id')
            ->map(fn ($id) => (int) $id)
            ->all();
    }

    private function fecha(Audiencia $a): string
    {
        return $a->fecha_hora
            ? \Carbon\Carbon::parse($a->fecha_hora)->format('d/m/Y H:i')
            : 'sin fecha';
    }

    public function created(Audiencia $a): void
    {
        $destinatarios = Notificador::sinActor(array_merge(
            $this->abogadosDelJuicio((int) $a->juicio_id),
            Notificador::asistentes(),
            Notificador::admins()
        ));

        Notificador::enviar($destinatarios, Notificador::payload(
            'Nueva audiencia programada',
            "Audiencia {$a->tipo_audiencia} para el {$this->fecha($a)}.",
            '/calendario',
            'audiencia',
            'fas fa-calendar-check',
            'info',
            'audiencia',
            $a->id
        ), "audiencia:{$a->id}:creada");
    }

    public function updated(Audiencia $a): void
    {
        if ($a->wasChanged('fecha_hora') || ($a->wasChanged('estado') && $a->estado === 'Programada')) {
            $destinatarios = array_merge(
                $this->abogadosDelJuicio((int) $a->juicio_id),
                Notificador::asistentes(),
                Notificador::admins()
            );
            Notificador::enviar(Notificador::sinActor($destinatarios), Notificador::payload(
                'Audiencia reprogramada',
                "Audiencia {$a->tipo_audiencia} para el {$this->fecha($a)}.",
                '/calendario',
                'audiencia',
                'fas fa-calendar-alt',
                'warning',
                'audiencia',
                $a->id
            ), "audiencia:{$a->id}:reprogramada:{$a->fecha_hora}");
        }

        if ($a->wasChanged('estado') && in_array($a->estado, ['Realizada', 'Suspendida'], true)) {
            $destinatarios = array_merge(Notificador::admins(), Notificador::asistentes());
            Notificador::enviar(Notificador::sinActor($destinatarios), Notificador::payload(
                'Audiencia ' . strtolower($a->estado),
                "La audiencia {$a->tipo_audiencia} del {$this->fecha($a)} fue {$a->estado}.",
                '/calendario',
                'audiencia',
                'fas fa-calendar-times',
                $a->estado === 'Suspendida' ? 'danger' : 'success',
                'audiencia',
                $a->id
            ), "audiencia:{$a->id}:estado:{$a->estado}");
        }
    }
}

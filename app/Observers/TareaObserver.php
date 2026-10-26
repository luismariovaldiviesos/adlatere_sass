<?php

namespace App\Observers;

use App\Models\Tarea;
use App\Services\Notificador;

class TareaObserver
{
    public function created(Tarea $t): void
    {
        if (!$t->asignado_a) {
            return;
        }

        $destinatarios = Notificador::sinActor([$t->asignado_a]);

        Notificador::enviar($destinatarios, Notificador::payload(
            'Nueva tarea asignada',
            $t->titulo,
            '/tareas',
            'tarea',
            'fas fa-tasks',
            'info',
            'tarea',
            $t->id
        ));
    }

    public function updated(Tarea $t): void
    {
        $actor = auth()->user();
        $actorName = $actor ? $actor->name : 'Un usuario';

        if ($t->wasChanged('asignado_a') && $t->asignado_a) {
            Notificador::enviar(Notificador::sinActor([$t->asignado_a]), Notificador::payload(
                'Tarea reasignada',
                "Se te asignó la tarea: {$t->titulo}",
                '/tareas',
                'tarea',
                'fas fa-tasks',
                'info',
                'tarea',
                $t->id
            ));
        }

        if ($t->wasChanged('estado')) {
            if ($t->estado === 'completada') {
                $destinatarios = array_merge(Notificador::admins(), [(int) $t->creado_por]);
                Notificador::enviar(Notificador::sinActor($destinatarios), Notificador::payload(
                    'Tarea completada',
                    "{$actorName} completó la tarea: {$t->titulo}",
                    '/tareas',
                    'tarea',
                    'fas fa-check-circle',
                    'success',
                    'tarea',
                    $t->id
                ), "tarea:{$t->id}:completada");
            }

            if ($t->estado === 'convertida') {
                Notificador::enviar(Notificador::sinActor(Notificador::admins()), Notificador::payload(
                    'Tarea convertida en actividad',
                    "{$actorName} convirtió la tarea: {$t->titulo}",
                    '/tareas',
                    'tarea',
                    'fas fa-gavel',
                    'info',
                    'tarea',
                    $t->id
                ), "tarea:{$t->id}:convertida");
            }
        }

        if ($t->wasChanged('fecha_limite') && $t->asignado_a) {
            $fecha = $t->fecha_limite ? $t->fecha_limite->format('d/m/Y') : 'sin fecha';
            Notificador::enviar(Notificador::sinActor([$t->asignado_a]), Notificador::payload(
                'Fecha de vencimiento actualizada',
                "La tarea \"{$t->titulo}\" vence el {$fecha}.",
                '/tareas',
                'tarea',
                'fas fa-calendar-alt',
                'warning',
                'tarea',
                $t->id
            ), "tarea:{$t->id}:fecha:" . ($t->fecha_limite ? $t->fecha_limite->format('Y-m-d') : 'null'));
        }
    }
}

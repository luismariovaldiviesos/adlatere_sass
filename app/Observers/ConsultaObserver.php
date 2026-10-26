<?php

namespace App\Observers;

use App\Models\Consulta;
use App\Services\Notificador;

class ConsultaObserver
{
    public function created(Consulta $c): void
    {
        if (!$c->abogado_id) {
            return;
        }

        Notificador::enviar(Notificador::sinActor([$c->abogado_id]), Notificador::payload(
            'Nueva consulta asignada',
            "Se te asignó la consulta #{$c->id}.",
            Notificador::urlConsultasPara((int) $c->abogado_id),
            'consulta',
            'fas fa-comments',
            'info',
            'consulta',
            $c->id
        ));
    }

    public function updated(Consulta $c): void
    {
        if ($c->wasChanged('abogado_id') && $c->abogado_id) {
            Notificador::enviar(Notificador::sinActor([$c->abogado_id]), Notificador::payload(
                'Consulta reasignada',
                "Se te asignó la consulta #{$c->id}.",
                Notificador::urlConsultasPara((int) $c->abogado_id),
                'consulta',
                'fas fa-comments',
                'info',
                'consulta',
                $c->id
            ));
        }

        if ($c->wasChanged('estado_pago') && in_array($c->estado_pago, ['pagada', 'en_facturacion', 'facturada'], true)) {
            $estados = [
                'pagada' => 'pagada',
                'en_facturacion' => 'enviada a facturación',
                'facturada' => 'facturada',
            ];
            $destinatarios = array_merge(Notificador::admins(), Notificador::asistentes());
            Notificador::enviar(Notificador::sinActor($destinatarios), Notificador::payload(
                'Consulta actualizada',
                "La consulta #{$c->id} fue {$estados[$c->estado_pago]}.",
                '/consultas',
                'consulta',
                'fas fa-money-bill-wave',
                'success',
                'consulta',
                $c->id
            ), "consulta:{$c->id}:pago:{$c->estado_pago}");
        }

        if ($c->wasChanged('estado_atencion') && $c->estado_atencion === 'atendida') {
            Notificador::enviar(Notificador::sinActor(Notificador::admins()), Notificador::payload(
                'Consulta atendida',
                "La consulta #{$c->id} fue marcada como atendida.",
                '/consultas',
                'consulta',
                'fas fa-check-circle',
                'success',
                'consulta',
                $c->id
            ), "consulta:{$c->id}:atendida");
        }
    }
}

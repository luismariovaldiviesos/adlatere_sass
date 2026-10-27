<?php

namespace App\Observers;

use App\Models\Actividad;
use App\Services\ActividadFirmaNotifier;

class ActividadObserver
{
    public function created(Actividad $actividad): void
    {
        ActividadFirmaNotifier::notificarPendiente($actividad);
    }

    public function updated(Actividad $actividad): void
    {
        if ($actividad->wasChanged('estado_firma') || $actividad->wasChanged('tipo_actividad_id')) {
            if (strtolower((string) $actividad->estado_firma) === 'pendiente' && ($actividad->tipoActividad->es_firmable ?? false)) {
                ActividadFirmaNotifier::notificarPendiente($actividad);
            } else {
                ActividadFirmaNotifier::limpiarPendiente($actividad);
            }
        }
    }

    public function deleted(Actividad $actividad): void
    {
        ActividadFirmaNotifier::limpiarPendiente($actividad);
    }
}

<?php

namespace App\Services;

use App\Models\Actividad;
use Illuminate\Support\Facades\DB;

class ActividadFirmaNotifier
{
    public static function notificarPendiente(Actividad $actividad): void
    {
        $actividad->loadMissing(['tipoActividad', 'juicio']);

        if (strtolower((string) $actividad->estado_firma) !== 'pendiente') {
            return;
        }

        if (!($actividad->tipoActividad->es_firmable ?? false)) {
            return;
        }

        $codigo = $actividad->juicio->cod_satje ?? ('#' . $actividad->juicio_id);
        $tipo = $actividad->tipoActividad->nombre ?? 'Actividad';

        Notificador::enviar(
            self::destinatarios($actividad),
            Notificador::payload(
                'Actividad pendiente de firma',
                "El {$tipo} del juicio {$codigo} está pendiente de firma.",
                '/juicios?juicio=' . $actividad->juicio_id,
                'firma',
                'fas fa-file-signature',
                'warning',
                'actividad',
                $actividad->id
            ),
            "actividad:{$actividad->id}:firma_pendiente"
        );
    }

    public static function limpiarPendiente(Actividad $actividad): void
    {
        Notificador::eliminarPorDedupe("actividad:{$actividad->id}:firma_pendiente");
    }

    public static function destinatarios(Actividad $actividad): array
    {
        $patrocinadores = DB::table('juicio_user')
            ->where('juicio_id', $actividad->juicio_id)
            ->whereRaw("TRIM(LOWER(rol_en_juicio)) LIKE ?", ['%abogado%patrocinador%'])
            ->pluck('user_id')
            ->map(fn ($id) => (int) $id)
            ->all();

        return Notificador::sinActor(array_merge($patrocinadores, Notificador::admins()));
    }
}

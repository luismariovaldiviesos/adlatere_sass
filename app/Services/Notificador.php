<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class Notificador
{
    public const TYPE = 'App\\Notifications\\SistemaNotificacion';

    /**
     * IDs de usuarios activos por perfil (cacheado para evitar consultas por evento).
     */
    public static function usuariosPorPerfil(array $perfiles): array
    {
        $tenant = function_exists('tenant') ? tenant() : null;
        $tenantKey = $tenant ? $tenant->getTenantKey() : 'central';
        $key = 'notif.perfiles.' . $tenantKey . '.' . md5(implode(',', $perfiles));

        return Cache::remember($key, 300, function () use ($perfiles) {
            return User::whereIn('profile', $perfiles)
                ->where('status', 'ACTIVE')
                ->pluck('id')
                ->map(fn ($id) => (int) $id)
                ->all();
        });
    }

    public static function admins(): array
    {
        return self::usuariosPorPerfil(['Admin']);
    }

    public static function asistentes(): array
    {
        return self::usuariosPorPerfil(['Asistente']);
    }

    public static function abogados(): array
    {
        return self::usuariosPorPerfil(['Abogado', 'Pasante']);
    }

    /**
     * Inserta una notificación (una fila por destinatario) sin bloquear la acción de negocio.
     * Si se entrega dedupeKey, insertOrIgnore evita duplicados para el mismo usuario.
     */
    public static function enviar(array $userIds, array $data, ?string $dedupeKey = null): void
    {
        $userIds = array_values(array_unique(array_filter(array_map('intval', $userIds))));
        if (empty($userIds)) {
            return;
        }

        $now = now();
        $payload = json_encode($data, JSON_UNESCAPED_UNICODE);
        $rows = [];

        foreach ($userIds as $uid) {
            $rows[] = [
                'id' => (string) Str::uuid(),
                'type' => self::TYPE,
                'notifiable_type' => User::class,
                'notifiable_id' => $uid,
                'data' => $payload,
                'read_at' => null,
                'dedupe_key' => $dedupeKey,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        try {
            DB::table('notifications')->insertOrIgnore($rows);
        } catch (\Throwable $e) {
            \Log::warning('No se pudo insertar la notificación: ' . $e->getMessage());
        }
    }

    public static function eliminarPorDedupe(string $dedupeKey): void
    {
        try {
            DB::table('notifications')->where('dedupe_key', $dedupeKey)->delete();
        } catch (\Throwable $e) {
            \Log::warning('No se pudo eliminar la notificación: ' . $e->getMessage());
        }
    }

    public static function enviarAAdmins(array $data, ?string $dedupeKey = null): void
    {
        self::enviar(self::admins(), $data, $dedupeKey);
    }

    public static function enviarAAsistentes(array $data, ?string $dedupeKey = null): void
    {
        self::enviar(self::asistentes(), $data, $dedupeKey);
    }

    public static function sinActor(array $ids): array
    {
        $actor = auth()->id();

        return array_values(array_filter($ids, fn ($id) => (int) $id !== (int) $actor));
    }

    public static function urlConsultasPara(int $userId): string
    {
        $perfil = User::whereKey($userId)->value('profile');

        return in_array($perfil, ['Abogado', 'Pasante'], true) ? '/mis-consultas' : '/consultas';
    }

    /**
     * Payload estándar para mantener consistencia en la campana y la bandeja.
     */
    public static function payload(
        string $titulo,
        string $mensaje,
        string $url,
        string $categoria,
        string $icono = 'fas fa-bell',
        string $prioridad = 'info',
        ?string $refTipo = null,
        ?int $refId = null
    ): array {
        return [
            'titulo' => $titulo,
            'mensaje' => $mensaje,
            'url' => $url,
            'categoria' => $categoria,
            'icono' => $icono,
            'prioridad' => $prioridad,
            'ref_tipo' => $refTipo,
            'ref_id' => $refId,
            'actor_id' => auth()->id(),
        ];
    }
}

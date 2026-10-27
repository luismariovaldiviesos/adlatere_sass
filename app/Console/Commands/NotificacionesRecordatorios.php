<?php

namespace App\Console\Commands;

use App\Models\Actividad;
use App\Models\Audiencia;
use App\Models\Consulta;
use App\Models\PagosJuicio;
use App\Models\Tarea;
use App\Models\User;
use App\Models\XmlFile;
use App\Services\ActividadFirmaNotifier;
use App\Services\Notificador;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class NotificacionesRecordatorios extends Command
{
    protected $signature = 'notificaciones:recordatorios';

    protected $description = 'Genera recordatorios internos de tareas, audiencias y consultas';

    public function handle()
    {
        $this->recordatoriosTareas();
        $this->recordatoriosAudiencias();
        $this->recordatoriosAudienciasProgramadas();
        $this->recordatoriosConsultas();
        $this->recordatoriosActividadesFirma();
        $this->recordatoriosPendientesAbogados();
        $this->recordatoriosPendientesAdmin();
        $this->recordatoriosAudienciasVencidas();
        $this->recordatoriosFirmasAdmin();
        $this->recordatoriosFacturacion();
        $this->recordatoriosCobros();

        $this->info('Recordatorios procesados: ' . now()->format('d/m/Y H:i'));

        return 0;
    }

    private function recordatoriosPendientesAbogados(): void
    {
        $fecha = now()->format('Y-m-d');

        $tareas = Tarea::query()
            ->whereIn('estado', ['pendiente', 'en_proceso'])
            ->whereNotNull('asignado_a')
            ->selectRaw('asignado_a, COUNT(*) as total')
            ->groupBy('asignado_a')
            ->pluck('total', 'asignado_a');

        foreach ($tareas as $uid => $total) {
            Notificador::enviar([(int) $uid], Notificador::payload(
                'Tareas pendientes',
                "Tienes {$total} tarea(s) pendiente(s).",
                '/tareas',
                'tarea',
                'fas fa-tasks',
                'info',
                'recordatorio'
            ), "rem:abogado:tareas_pendientes:{$fecha}");
        }

        $consultas = Consulta::query()
            ->where('estado_atencion', 'pendiente')
            ->whereNotNull('abogado_id')
            ->selectRaw('abogado_id, COUNT(*) as total')
            ->groupBy('abogado_id')
            ->pluck('total', 'abogado_id');

        foreach ($consultas as $uid => $total) {
            Notificador::enviar([(int) $uid], Notificador::payload(
                'Consultas pendientes',
                "Tienes {$total} consulta(s) pendiente(s) de atención.",
                Notificador::urlConsultasPara((int) $uid),
                'consulta',
                'fas fa-comments',
                'info',
                'recordatorio'
            ), "rem:abogado:consultas_pendientes:{$fecha}");
        }
    }

    private function recordatoriosPendientesAdmin(): void
    {
        $fecha = now()->format('Y-m-d');

        $tareas = Tarea::query()
            ->whereIn('estado', ['pendiente', 'en_proceso'])
            ->whereNotNull('asignado_a')
            ->selectRaw('asignado_a, COUNT(*) as total')
            ->groupBy('asignado_a')
            ->pluck('total', 'asignado_a');

        if ($tareas->isNotEmpty()) {
            $nombres = User::whereIn('id', $tareas->keys())->pluck('name', 'id');
            $detalle = $tareas->map(fn ($total, $id) => ($nombres[$id] ?? ('Abogado #' . $id)) . ': ' . $total)->take(8)->implode('; ');

            Notificador::enviar(Notificador::admins(), Notificador::payload(
                'Tareas pendientes por abogado',
                'Pendientes: ' . $detalle,
                '/tareas',
                'tarea',
                'fas fa-tasks',
                'warning',
                'recordatorio'
            ), "rem:admin:tareas_pendientes:{$fecha}");
        }

        $consultas = Consulta::query()
            ->where('estado_atencion', 'pendiente')
            ->whereNotNull('abogado_id')
            ->selectRaw('abogado_id, COUNT(*) as total')
            ->groupBy('abogado_id')
            ->pluck('total', 'abogado_id');

        if ($consultas->isNotEmpty()) {
            $nombres = User::whereIn('id', $consultas->keys())->pluck('name', 'id');
            $detalle = $consultas->map(fn ($total, $id) => ($nombres[$id] ?? ('Abogado #' . $id)) . ': ' . $total)->take(8)->implode('; ');

            Notificador::enviar(Notificador::admins(), Notificador::payload(
                'Consultas pendientes por abogado',
                'Pendientes: ' . $detalle,
                '/consultas',
                'consulta',
                'fas fa-comments',
                'warning',
                'recordatorio'
            ), "rem:admin:consultas_pendientes:{$fecha}");
        }
    }

    private function recordatoriosAudienciasVencidas(): void
    {
        $items = Audiencia::query()
            ->where('estado', 'Programada')
            ->where('fecha_hora', '<', now())
            ->get(['id', 'juicio_id']);

        $this->digestAudiencias(
            $items,
            'Audiencias vencidas sin actualizar',
            'rem:audiencias:vencidas:' . now()->format('Y-m-d'),
            'fas fa-calendar-times',
            'danger'
        );
    }

    private function recordatoriosFirmasAdmin(): void
    {
        $actividades = Actividad::with('juicio')
            ->where('estado_firma', 'pendiente')
            ->whereHas('tipoActividad', fn ($q) => $q->where('es_firmable', true))
            ->get();

        if ($actividades->isEmpty()) {
            return;
        }

        $porJuicio = $actividades
            ->groupBy(fn ($a) => $a->juicio->cod_satje ?? ('#' . $a->juicio_id))
            ->map->count();

        $detalle = $porJuicio
            ->map(fn ($total, $juicio) => $juicio . ': ' . $total)
            ->take(8)
            ->implode('; ');

        Notificador::enviar(Notificador::admins(), Notificador::payload(
            'Actividades pendientes de firma',
            'Pendientes por juicio: ' . $detalle,
            '/juicios',
            'firma',
            'fas fa-file-signature',
            'warning',
            'recordatorio'
        ), 'rem:admin:firmas:' . now()->format('Y-m-d'));
    }

    private function recordatoriosFacturacion(): void
    {
        $xmlPendientes = XmlFile::query()
            ->where('estado', '!=', 'autorizado')
            ->whereHas('factura', fn ($q) => $q->whereNull('deleted_at'))
            ->count();

        if ($xmlPendientes > 0) {
            Notificador::enviar(array_merge(Notificador::asistentes(), Notificador::admins()), Notificador::payload(
                'Facturas pendientes de reprocesar',
                "Hay {$xmlPendientes} factura(s) pendiente(s) de autorización o reproceso.",
                '/reprocesar',
                'factura',
                'fas fa-receipt',
                'warning',
                'recordatorio'
            ), 'rem:facturas:reprocesar:' . now()->format('Y-m-d'));
        }
    }

    private function recordatoriosCobros(): void
    {
        $pagos = PagosJuicio::query()
            ->where('estado', 'Aprobado')
            ->whereNull('factura_id')
            ->count();

        if ($pagos > 0) {
            Notificador::enviar(array_merge(Notificador::asistentes(), Notificador::admins()), Notificador::payload(
                'Cobros pendientes de facturar',
                "Hay {$pagos} abono(s) aprobado(s) sin factura.",
                '/finanzas-juicios',
                'cobro',
                'fas fa-money-bill-wave',
                'info',
                'recordatorio'
            ), 'rem:cobros:pagos_sin_factura:' . now()->format('Y-m-d'));
        }

        $consultas = Consulta::query()
            ->where('estado_pago', 'pagada')
            ->whereNull('factura_id')
            ->count();

        if ($consultas > 0) {
            Notificador::enviar(array_merge(Notificador::asistentes(), Notificador::admins()), Notificador::payload(
                'Consultas pagadas sin facturar',
                "Hay {$consultas} consulta(s) pagada(s) sin factura.",
                '/consultas',
                'cobro',
                'fas fa-money-bill-wave',
                'warning',
                'recordatorio'
            ), 'rem:cobros:consultas_sin_factura:' . now()->format('Y-m-d'));
        }
    }

    private function recordatoriosActividadesFirma(): void
    {
        Actividad::with(['tipoActividad', 'juicio'])
            ->where('estado_firma', 'pendiente')
            ->whereHas('tipoActividad', fn ($q) => $q->where('es_firmable', true))
            ->orderBy('id')
            ->chunkById(100, function ($actividades) {
                foreach ($actividades as $actividad) {
                    ActividadFirmaNotifier::notificarPendiente($actividad);
                }
            });
    }

    private function recordatoriosTareas(): void
    {
        $base = Tarea::query()
            ->whereIn('estado', ['pendiente', 'en_proceso'])
            ->whereNotNull('fecha_limite')
            ->whereNotNull('asignado_a');

        $vencidas = (clone $base)
            ->whereDate('fecha_limite', '<', now()->toDateString())
            ->get(['id', 'asignado_a']);

        $porVencer = (clone $base)
            ->whereDate('fecha_limite', '>=', now()->toDateString())
            ->whereDate('fecha_limite', '<=', now()->addDay()->toDateString())
            ->get(['id', 'asignado_a']);

        $this->digestPorAsignado(
            $vencidas,
            'Tareas vencidas',
            'Tienes %d tarea(s) vencida(s) sin completar.',
            'rem:tareas:vencidas:' . now()->format('Y-m-d'),
            'fas fa-exclamation-triangle',
            'danger'
        );

        $this->digestPorAsignado(
            $porVencer,
            'Tareas por vencer',
            'Tienes %d tarea(s) que vencen en las próximas 24 horas.',
            'rem:tareas:por_vencer:' . $this->bucket(6),
            'fas fa-clock',
            'warning'
        );

        if ($vencidas->isNotEmpty()) {
            $excluir = $vencidas->pluck('asignado_a')->map(fn ($v) => (int) $v)->all();
            $destinatarios = array_diff(array_merge(Notificador::asistentes(), Notificador::admins()), $excluir);
            Notificador::enviar($destinatarios, Notificador::payload(
                'Tareas vencidas',
                "Hay {$vencidas->count()} tarea(s) vencida(s) en el sistema.",
                '/tareas',
                'tarea',
                'fas fa-exclamation-triangle',
                'danger',
                'recordatorio'
            ), 'rem:tareas:vencidas:global:' . now()->format('Y-m-d'));
        }

        if ($porVencer->isNotEmpty()) {
            $excluir = $porVencer->pluck('asignado_a')->map(fn ($v) => (int) $v)->all();
            $destinatarios = array_diff(array_merge(Notificador::asistentes(), Notificador::admins()), $excluir);
            Notificador::enviar($destinatarios, Notificador::payload(
                'Tareas por vencer',
                "Hay {$porVencer->count()} tarea(s) que vencen en las próximas 24 horas.",
                '/tareas',
                'tarea',
                'fas fa-clock',
                'warning',
                'recordatorio'
            ), 'rem:tareas:por_vencer:global:' . $this->bucket(6));
        }
    }

    private function digestPorAsignado($items, string $titulo, string $mensaje, string $dedupe, string $icono, string $prioridad): void
    {
        if ($items->isEmpty()) {
            return;
        }

        foreach ($items->groupBy('asignado_a') as $uid => $lista) {
            Notificador::enviar([(int) $uid], Notificador::payload(
                $titulo,
                sprintf($mensaje, $lista->count()),
                '/tareas',
                'tarea',
                $icono,
                $prioridad,
                'recordatorio'
            ), $dedupe);
        }
    }

    private function recordatoriosAudienciasProgramadas(): void
    {
        $items = Audiencia::query()
            ->where('estado', 'Programada')
            ->where('fecha_hora', '>=', now())
            ->get(['id', 'juicio_id', 'tipo_audiencia', 'fecha_hora']);

        foreach ($items as $audiencia) {
            $abogados = DB::table('juicio_user')
                ->where('juicio_id', $audiencia->juicio_id)
                ->pluck('user_id')
                ->map(fn ($id) => (int) $id)
                ->all();

            if (empty($abogados)) {
                continue;
            }

            $fecha = $audiencia->fecha_hora
                ? \Carbon\Carbon::parse($audiencia->fecha_hora)->format('d/m/Y H:i')
                : 'sin fecha';

            Notificador::enviar($abogados, Notificador::payload(
                'Audiencia programada',
                "Audiencia {$audiencia->tipo_audiencia} para el {$fecha}.",
                '/calendario',
                'audiencia',
                'fas fa-calendar-check',
                'info',
                'audiencia',
                $audiencia->id
            ), "audiencia:{$audiencia->id}:creada");
        }
    }

    private function recordatoriosAudiencias(): void
    {
        $base = Audiencia::query()->where('estado', 'Programada');

        $en24 = (clone $base)
            ->whereBetween('fecha_hora', [now(), now()->addDay()])
            ->get(['id', 'juicio_id']);

        $en2 = (clone $base)
            ->whereBetween('fecha_hora', [now(), now()->addHours(2)])
            ->get(['id', 'juicio_id']);

        $this->digestAudiencias(
            $en24,
            'Audiencias en las próximas 24 horas',
            'rem:audiencias:24h:' . $this->bucket(6),
            'fas fa-calendar-check',
            'info'
        );

        $this->digestAudiencias(
            $en2,
            'Audiencias en las próximas 2 horas',
            'rem:audiencias:2h:' . $this->bucket(1),
            'fas fa-bell',
            'warning'
        );
    }

    private function digestAudiencias($items, string $titulo, string $dedupe, string $icono, string $prioridad): void
    {
        if ($items->isEmpty()) {
            return;
        }

        $juicioIds = $items->pluck('juicio_id')->unique()->filter()->values()->all();

        $pivotes = DB::table('juicio_user')
            ->whereIn('juicio_id', $juicioIds)
            ->get(['juicio_id', 'user_id']);

        $abogadosPorJuicio = $pivotes
            ->groupBy('juicio_id')
            ->map(fn ($g) => $g->pluck('user_id')->map(fn ($v) => (int) $v)->all());

        $conteo = [];
        foreach ($items as $a) {
            foreach ($abogadosPorJuicio->get($a->juicio_id, []) as $uid) {
                $conteo[$uid] = ($conteo[$uid] ?? 0) + 1;
            }
        }

        foreach (Notificador::asistentes() as $uid) {
            $conteo[$uid] = $items->count();
        }

        foreach (Notificador::admins() as $uid) {
            $conteo[$uid] = $items->count();
        }

        foreach ($conteo as $uid => $total) {
            Notificador::enviar([(int) $uid], Notificador::payload(
                $titulo,
                "Tienes {$total} audiencia(s) programada(s). Revisa el calendario.",
                '/calendario',
                'audiencia',
                $icono,
                $prioridad,
                'recordatorio'
            ), $dedupe);
        }
    }

    private function recordatoriosConsultas(): void
    {
        $sinAtender = Consulta::query()
            ->where('estado_atencion', 'pendiente')
            ->where('created_at', '<=', now()->subDays(2))
            ->whereNotNull('abogado_id')
            ->get(['id', 'abogado_id']);

        if ($sinAtender->isNotEmpty()) {
            foreach ($sinAtender->groupBy('abogado_id') as $uid => $lista) {
                Notificador::enviar([(int) $uid], Notificador::payload(
                    'Consultas pendientes de atención',
                    "Tienes {$lista->count()} consulta(s) sin atender por más de 2 días.",
                    Notificador::urlConsultasPara((int) $uid),
                    'consulta',
                    'fas fa-comments',
                    'warning',
                    'recordatorio'
                ), 'rem:consultas:sin_atender:' . now()->format('Y-m-d'));
            }

            $excluir = $sinAtender->pluck('abogado_id')->map(fn ($v) => (int) $v)->all();
            $destinatarios = array_diff(Notificador::admins(), $excluir);
            Notificador::enviar($destinatarios, Notificador::payload(
                'Consultas pendientes de atención',
                "Hay {$sinAtender->count()} consulta(s) sin atender por más de 2 días.",
                '/consultas',
                'consulta',
                'fas fa-comments',
                'warning',
                'recordatorio'
            ), 'rem:consultas:sin_atender:global:' . now()->format('Y-m-d'));
        }

        $porPagar = Consulta::query()
            ->whereIn('estado_pago', ['pendiente', 'en_facturacion'])
            ->where('created_at', '<=', now()->subDay())
            ->get(['id']);

        if ($porPagar->isNotEmpty()) {
            Notificador::enviar(array_merge(Notificador::asistentes(), Notificador::admins()), Notificador::payload(
                'Consultas pendientes de pago',
                "Hay {$porPagar->count()} consulta(s) pendiente(s) de pago o facturación.",
                '/consultas',
                'consulta',
                'fas fa-money-bill-wave',
                'info',
                'recordatorio'
            ), 'rem:consultas:por_pagar:' . now()->format('Y-m-d'));
        }
    }

    private function bucket(int $horas): string
    {
        return now()->format('Y-m-d') . '-' . intdiv((int) now()->format('G'), $horas);
    }
}

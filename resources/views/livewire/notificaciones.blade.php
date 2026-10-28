<div class="relative" x-data="{}" x-on:click.outside="$wire.cerrar()">
    <style>
        @keyframes notifRing {
            0%, 100% { transform: rotate(0); }
            15% { transform: rotate(-14deg); }
            30% { transform: rotate(12deg); }
            45% { transform: rotate(-9deg); }
            60% { transform: rotate(7deg); }
            75% { transform: rotate(-4deg); }
        }
        @keyframes notifPing {
            0% { transform: scale(.9); opacity: .95; }
            70% { transform: scale(1.35); opacity: 0; }
            100% { transform: scale(1.35); opacity: 0; }
        }
        .notif-bell {
            position: relative;
            display: flex;
            align-items: center;
            justify-content: center;
            width: 48px;
            height: 48px;
            border-radius: 9999px;
            border: 2px solid #ffffff;
            background: linear-gradient(135deg, #f59e0b 0%, #ef4444 50%, #8b5cf6 100%);
            color: #ffffff;
            box-shadow: 0 8px 22px rgba(239, 68, 68, .45), 0 2px 6px rgba(0, 0, 0, .2);
            transition: transform .18s ease, box-shadow .18s ease;
        }
        .notif-bell:hover {
            transform: translateY(-1px) scale(1.06);
            box-shadow: 0 12px 28px rgba(239, 68, 68, .6), 0 3px 8px rgba(0, 0, 0, .25);
        }
        .notif-bell:active { transform: scale(.97); }
        .notif-bell:hover .notif-bell-icon { animation: notifRing .6s ease-in-out; }
        .notif-bell-icon {
            font-size: 22px;
            line-height: 1;
            filter: drop-shadow(0 1px 2px rgba(0, 0, 0, .35));
        }
        .notif-badge {
            position: absolute;
            top: 0;
            right: 0;
            z-index: 4;
            min-width: 24px;
            height: 24px;
            padding: 0 5px;
            border-radius: 9999px;
            background: linear-gradient(135deg, #f97316, #dc2626);
            color: #ffffff;
            font-size: 12px;
            font-weight: 800;
            line-height: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 2px solid #ffffff;
            box-shadow: 0 2px 6px rgba(0, 0, 0, .35);
        }
    </style>

    <button type="button" wire:click="toggle" class="notif-bell" title="Notificaciones{{ $count > 0 ? ' (' . $count . ' sin leer)' : '' }}">
        @if($count > 0)
        <span style="position:absolute;top:-4px;right:-4px;bottom:-4px;left:-4px;border-radius:9999px;border:2px solid rgba(239,68,68,.65);animation:notifPing 2s ease-out infinite;pointer-events:none;"></span>
        @endif
        <i class="fas fa-bell notif-bell-icon"></i>
        @if($count > 0)
        <span class="notif-badge">{{ $count > 99 ? '99+' : $count }}</span>
        @endif
    </button>

    @if($open)
    <div class="absolute right-0 mt-2 bg-white border border-gray-200 rounded-lg shadow-xl overflow-hidden" style="width: 360px; max-width: calc(100vw - 24px); z-index: 200;">
        <div style="display:flex;align-items:center;justify-content:space-between;padding:10px 16px;background:linear-gradient(90deg,#4f46e5,#9333ea);">
            <span style="color:#ffffff;font-weight:800;font-size:12px;letter-spacing:.4px;">NOTIFICACIONES</span>
            <button type="button" wire:click="marcarTodas" style="color:#ffffff;font-size:11px;text-decoration:underline;opacity:.95;">Marcar todas</button>
        </div>
        <div class="max-h-96 overflow-y-auto">
            @forelse($items as $n)
                @php
                    $d = $n->data;
                    $unread = !$n->read_at;
                    $prio = $d['prioridad'] ?? 'info';
                    $iconBg = ['success' => '#dcfce7', 'warning' => '#fef3c7', 'danger' => '#fee2e2', 'info' => '#e0e7ff'][$prio] ?? '#e5e7eb';
                    $iconColor = ['success' => '#16a34a', 'warning' => '#d97706', 'danger' => '#dc2626', 'info' => '#4f46e5'][$prio] ?? '#6b7280';
                @endphp
                <button type="button" wire:click="ir('{{ $n->id }}')"
                    class="w-full text-left px-4 py-3 border-b last:border-b-0 hover:bg-gray-50 {{ $unread ? 'bg-blue-50' : '' }}">
                    <div class="flex gap-2">
                        <span style="display:flex;align-items:center;justify-content:center;width:32px;height:32px;border-radius:9999px;background:{{ $iconBg }};color:{{ $iconColor }};flex:0 0 32px;">
                            <i class="{{ $d['icono'] ?? 'fas fa-bell' }}"></i>
                        </span>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-semibold text-gray-800 truncate">{{ $d['titulo'] ?? 'Notificación' }}</p>
                            <p class="text-xs text-gray-600">{{ $d['mensaje'] ?? '' }}</p>
                            <span class="text-[10px] text-gray-400">{{ $n->created_at->diffForHumans() }}</span>
                        </div>
                        @if($unread)
                        <span class="w-2 h-2 rounded-full bg-blue-500 mt-2"></span>
                        @endif
                    </div>
                </button>
            @empty
                <p class="px-4 py-6 text-sm text-gray-400 text-center">Sin notificaciones.</p>
            @endforelse
        </div>
        <div style="padding:8px 16px;border-top:1px solid #e5e7eb;text-align:center;background:#f9fafb;">
            <a href="/notificaciones" style="font-size:12px;font-weight:600;color:#4f46e5;">Ver todas</a>
        </div>
    </div>
    @endif
</div>

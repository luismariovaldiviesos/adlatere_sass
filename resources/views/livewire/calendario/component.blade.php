<div>
    <div class="intro-y col-span-12">
        <div class="intro-y box p-5">
            <div class="flex flex-wrap items-center justify-between gap-3 mb-4">
                <h2 class="text-lg font-medium">Calendario de audiencias</h2>
                <div class="flex items-center gap-4">
                    <div class="flex items-center gap-3 text-sm">
                        <span><span style="display:inline-block;width:12px;height:12px;border-radius:9999px;background-color:#3b82f6;"></span> Programada</span>
                        <span><span style="display:inline-block;width:12px;height:12px;border-radius:9999px;background-color:#22c55e;"></span> Realizada</span>
                        <span><span style="display:inline-block;width:12px;height:12px;border-radius:9999px;background-color:#eab308;"></span> Suspendida</span>
                    </div>
                    <select wire:model="filtroEstado" class="form-select w-auto">
                        <option value="todas">Todas</option>
                        <option value="Programada">Programadas</option>
                        <option value="Realizada">Realizadas</option>
                        <option value="Suspendida">Suspendidas</option>
                    </select>
                </div>
            </div>

            <div id="calendario-audiencias"></div>
        </div>
    </div>
</div>

<link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/index.global.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/index.global.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@fullcalendar/core@6.1.15/locales/es.global.js"></script>
<script>
    document.addEventListener('livewire:load', function () {
        var el = document.getElementById('calendario-audiencias');
        var calendar = new FullCalendar.Calendar(el, {
            locale: 'es',
            initialView: 'dayGridMonth',
            headerToolbar: {
                left: 'prev,next today',
                center: 'title',
                right: 'dayGridMonth,timeGridWeek,timeGridDay,listWeek'
            },
            buttonText: { today: 'Hoy', month: 'Mes', week: 'Semana', day: 'Día', list: 'Lista' },
            events: @json($eventos),
            dayMaxEvents: false,
            eventContent: function (arg) {
                var p = arg.event.extendedProps || {};
                function esc(s) {
                    return String(s ?? '—').replace(/[&<>"']/g, function (c) {
                        return {'&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;'}[c];
                    });
                }
                var sala = p.sala || '—';
                var esVirtual = /^https?:\/\//i.test(sala);
                var html = '<div style="white-space:normal;line-height:1.45;font-size:13px;overflow-wrap:anywhere;">'
                    + '<b style="font-size:14px;">' + esc(p.juicio) + '</b> · <b>' + esc(p.hora) + '</b>'
                    + '<br>Patrocinador: ' + esc(p.patrocinadores)
                    + '<br>' + (esVirtual ? 'Virtual' : esc(sala)) + ' · ' + esc(p.materia)
                    + '<br>Cliente(s): ' + esc(p.clientes)
                    + '</div>';
                return { html: html };
            }
        });
        calendar.render();

        window.addEventListener('calendario-actualizar', function (e) {
            calendar.removeAllEvents();
            calendar.addEventSource(e.detail.eventos);
        });
    });
</script>

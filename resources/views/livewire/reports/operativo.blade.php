<style>
    .rep-section { margin: 8px 0 0; padding: 0 20px 20px; }
    .rep-head {
        display:flex; justify-content:space-between; align-items:flex-end; gap:18px;
        padding: 4px 0 18px;
    }
    .rep-head h3 { margin:0; font-size:1.4rem; font-weight:800; letter-spacing:-.03em; color:#17233c; }
    .rep-head p { margin:5px 0 0; color:#7b8699; font-size:12px; }
    .rep-periodo {
        display:inline-flex; align-items:center; gap:7px; padding:7px 11px;
        border-radius:999px; background:#eef4ff; color:#345699; font-size:11px; font-weight:700;
        white-space:nowrap;
    }
    .rep-kpis {
        display:grid; grid-template-columns:repeat(5, minmax(0,1fr)); gap:10px;
    }
    .rep-kpi {
        background:#fff; border:1px solid #e4e9f2; border-radius:13px; padding:15px 16px;
    }
    .rep-kpi .label { color:#7f8ba0; font-size:10px; font-weight:800; letter-spacing:.08em; text-transform:uppercase; }
    .rep-kpi .value { margin-top:7px; color:#192a48; font-size:1.55rem; line-height:1; font-weight:800; letter-spacing:-.04em; }
    .rep-kpi .hint { margin-top:5px; color:#9aa5b7; font-size:10px; }
    .rep-kpi.warn .value { color:#b86a0b; }
    .rep-grid {
        display:grid; grid-template-columns: minmax(0,1.3fr) minmax(0,.9fr); gap:16px; margin-top:16px;
    }
    .rep-card {
        background:#fff; border:1px solid #e4e9f2; border-radius:15px; overflow:hidden;
        box-shadow:0 5px 18px rgba(15,31,64,.04);
    }
    .rep-card-head {
        display:flex; justify-content:space-between; align-items:center; gap:12px;
        padding:15px 17px; border-bottom:1px solid #edf0f6;
    }
    .rep-card-head h4 { margin:0; color:#1d2f4f; font-size:13px; font-weight:800; }
    .rep-card-head span { color:#8995a8; font-size:10px; }
    .rep-table { width:100%; border-collapse:collapse; font-size:12px; }
    .rep-table th {
        background:#f8faff; color:#7f8ba0; font-size:9px; text-transform:uppercase;
        letter-spacing:.07em; text-align:left; padding:9px 13px; font-weight:800;
    }
    .rep-table td { padding:10px 13px; border-top:1px solid #f0f2f7; color:#506079; }
    .rep-table td:first-child { color:#293a58; font-weight:700; }
    .rep-table .num { text-align:center; font-weight:700; color:#345a9b; }
    .rep-empty { padding:22px 16px; text-align:center; color:#97a2b4; font-size:11px; }
    .rep-list { list-style:none; margin:0; padding:0; }
    .rep-list li { padding:11px 16px; border-top:1px solid #f0f2f7; display:flex; gap:10px; align-items:flex-start; }
    .rep-list li:first-child { border-top:0; }
    .rep-dot { width:8px; height:8px; border-radius:50%; flex:0 0 auto; margin-top:5px; background:#3b6cd3; }
    .rep-dot.task { background:#f0a52b; }
    .rep-list strong { display:block; color:#2b3d5c; font-size:11.5px; line-height:1.4; }
    .rep-list span { display:block; color:#8995a8; font-size:10px; margin-top:1px; }
    .rep-amount { margin-left:auto; color:#1f6b43; font-size:10px; font-weight:800; white-space:nowrap; }
    .rep-date { margin-left:auto; color:#8d98ab; font-size:10px; white-space:nowrap; }
    @media (max-width: 980px) {
        .rep-kpis { grid-template-columns:repeat(2, minmax(0,1fr)); }
        .rep-grid { grid-template-columns:1fr; }
        .rep-head { align-items:flex-start; flex-direction:column; }
    }
</style>

<div class="rep-section">
    <div class="rep-head">
        <div>
            <div class="eyebrow dark" style="font-size:10px;color:#415b9a;font-weight:800;letter-spacing:.12em;text-transform:uppercase;">Reporte operativo</div>
            <h3>Radiografía del despacho</h3>
            <p>Carga procesal, actividad del equipo y próximos vencimientos del período seleccionado.</p>
        </div>
        <span class="rep-periodo">{{ $periodoLabel }}</span>
    </div>

    <div class="rep-kpis">
        <div class="rep-kpi">
            <div class="label">Casos activos</div>
            <div class="value">{{ $casosActivos }}</div>
            <div class="hint">Expedientes en el sistema</div>
        </div>
        <div class="rep-kpi">
            <div class="label">Audiencias</div>
            <div class="value">{{ $audienciasPeriodo }}</div>
            <div class="hint">En el período</div>
        </div>
        <div class="rep-kpi">
            <div class="label">Actuaciones</div>
            <div class="value">{{ $actuacionesPeriodo }}</div>
            <div class="hint">Registradas en el período</div>
        </div>
        <div class="rep-kpi warn">
            <div class="label">Tareas abiertas</div>
            <div class="value">{{ $tareasPendientes }}</div>
            <div class="hint">Pendientes o en proceso</div>
        </div>
        <div class="rep-kpi warn">
            <div class="label">Firmas pendientes</div>
            <div class="value">{{ $firmasPendientes }}</div>
            <div class="hint">Actividades por firmar</div>
        </div>
    </div>

    <div class="rep-grid">
        <section class="rep-card">
            <div class="rep-card-head">
                <h4>Carga por abogado</h4>
                <span>Casos · actuaciones · tareas</span>
            </div>
            @if(count($cargaAbogados))
                <div style="overflow-x:auto;">
                    <table class="rep-table">
                        <thead>
                            <tr>
                                <th>Abogado</th>
                                <th class="num">Casos</th>
                                <th class="num">Audiencias</th>
                                <th class="num">Actuaciones</th>
                                <th class="num">Tareas</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($cargaAbogados as $abogado)
                                <tr>
                                    <td>{{ $abogado['name'] }}</td>
                                    <td class="num">{{ $abogado['casos'] }}</td>
                                    <td class="num">{{ $abogado['audiencias'] }}</td>
                                    <td class="num">{{ $abogado['actuaciones'] }}</td>
                                    <td class="num">{{ $abogado['tareas'] }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="rep-empty">No hay abogados activos para mostrar.</div>
            @endif
        </section>

        <section class="rep-card">
            <div class="rep-card-head">
                <h4>Próximos 14 días</h4>
                <span>Agenda y tareas críticas</span>
            </div>
            <ul class="rep-list">
                @forelse($proximasAudiencias as $audiencia)
                    <li>
                        <span class="rep-dot"></span>
                        <div>
                            <strong>{{ $audiencia->tipo_audiencia }}</strong>
                            <span>{{ $audiencia->juicio->cod_satje ?? 'Juicio' }}</span>
                        </div>
                        <span class="rep-date">{{ \Carbon\Carbon::parse($audiencia->fecha_hora)->format('d/m H:i') }}</span>
                    </li>
                @empty
                    <li>
                        <span class="rep-dot"></span>
                        <div><strong>Sin audiencias programadas</strong><span>No hay audiencias en los próximos 14 días.</span></div>
                    </li>
                @endforelse
                @foreach($tareasCriticas as $tarea)
                    <li>
                        <span class="rep-dot task"></span>
                        <div>
                            <strong>{{ \Illuminate\Support\Str::limit($tarea->titulo, 45) }}</strong>
                            <span>{{ $tarea->asignado->name ?? 'Sin asignado' }} · {{ $tarea->juicio->cod_satje ?? 'Tarea externa' }}</span>
                        </div>
                        <span class="rep-date">{{ $tarea->fecha_limite ? $tarea->fecha_limite->format('d/m') : '—' }}</span>
                    </li>
                @endforeach
            </ul>
        </section>
    </div>
</div>

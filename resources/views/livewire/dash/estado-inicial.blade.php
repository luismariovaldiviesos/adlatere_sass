<style>
    .dash-status-wrap { margin-top: 1.25rem; }
    .dash-topbar {
        display: flex; align-items: flex-end; justify-content: space-between;
        gap: 18px; margin-bottom: 18px;
    }
    .dash-topbar h2 {
        margin: 0; font-size: 1.45rem; line-height: 1.2;
        font-weight: 800; letter-spacing: -.03em; color: #17233c;
    }
    .dash-topbar p { margin: 5px 0 0; color: #77839a; font-size: 12px; }
    .dash-year {
        display: flex; align-items: center; gap: 8px; flex: 0 0 auto;
        background: #fff; border: 1px solid #e3e8f1; border-radius: 10px;
        padding: 7px 9px 7px 12px;
    }
    .dash-year label { color: #68758b; font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: .05em; }
    .dash-year select {
        border: 0; background: transparent; color: #223557;
        font-size: 13px; font-weight: 700; outline: none; cursor: pointer;
    }
    .dash-status-grid {
        display: grid; grid-template-columns: minmax(240px, .78fr) minmax(0, 1.7fr);
        gap: 16px; align-items: stretch;
    }
    .dash-card {
        background: #fff; border: 1px solid #e3e8f1; border-radius: 16px;
        box-shadow: 0 6px 22px rgba(15, 31, 64, .05);
    }
    .dash-plan { padding: 22px; display: flex; flex-direction: column; }
    .dash-plan-head { display: flex; align-items: flex-start; justify-content: space-between; gap: 12px; }
    .dash-kicker { color: #7e8aa0; font-size: 10px; font-weight: 800; letter-spacing: .11em; text-transform: uppercase; }
    .dash-plan-name { margin: 7px 0 5px; font-size: 1.55rem; font-weight: 800; letter-spacing: -.04em; line-height: 1.15; color: #17233c; }
    .dash-plan-sub { margin: 0; color: #748096; font-size: 12px; }
    .dash-plan-icon {
        width: 40px; height: 40px; border-radius: 11px; display: grid; place-items: center;
        background: #eaf1ff; color: #2456bc; flex: 0 0 auto;
    }
    .dash-plan-icon svg { width: 21px; height: 21px; }
    .dash-status {
        display: inline-flex; align-items: center; gap: 5px;
        padding: 5px 9px; border-radius: 999px;
        font-size: 10px; font-weight: 800; letter-spacing: .03em; text-transform: uppercase;
    }
    .dash-status.active { background: #e9f9ee; color: #157442; }
    .dash-status.pending { background: #fff5df; color: #91610a; }
    .dash-status::before { content: ""; width: 6px; height: 6px; border-radius: 50%; background: currentColor; }
    .dash-plan-meta { margin-top: 20px; padding-top: 5px; border-top: 1px solid #edf0f6; }
    .dash-plan-meta-row { display: flex; align-items: center; justify-content: space-between; gap: 12px; padding: 8px 0; font-size: 12px; }
    .dash-plan-meta-row span { color: #7e8aa0; }
    .dash-plan-meta-row strong { color: #293a58; text-align: right; font-weight: 700; }
    .dash-checklist { padding: 21px 22px 18px; }
    .dash-checklist-head { display: flex; align-items: flex-start; justify-content: space-between; gap: 15px; }
    .dash-checklist-head h3 { margin: 6px 0 0; font-size: 1.06rem; font-weight: 800; letter-spacing: -.02em; color: #17233c; }
    .dash-progress { display: flex; align-items: center; gap: 10px; min-width: 140px; }
    .dash-progress-track { flex: 1; height: 7px; border-radius: 99px; background: #edf1f7; overflow: hidden; }
    .dash-progress-fill { height: 100%; border-radius: inherit; background: linear-gradient(90deg, #2a66d9, #22c55e); transition: width .3s ease; }
    .dash-progress-label { color: #53627b; font-size: 11px; font-weight: 800; white-space: nowrap; }
    .dash-progress.complete .dash-progress-fill { background: #22c55e; }
    .dash-checklist-list { list-style: none; margin: 18px 0 0; padding: 0; }
    .dash-check-item { display: flex; align-items: center; gap: 11px; padding: 9px 0; border-top: 1px solid #f0f2f7; }
    .dash-check-item:first-child { border-top: 0; }
    .dash-check-icon {
        width: 25px; height: 25px; flex: 0 0 auto; display: grid; place-items: center;
        border-radius: 50%; font-size: 12px; font-weight: 800;
    }
    .dash-check-icon.done { background: #e5f9eb; color: #159447; }
    .dash-check-icon.pending { background: #fff4df; color: #9b6809; }
    .dash-check-icon svg { width: 14px; height: 14px; }
    .dash-check-copy { min-width: 0; }
    .dash-check-copy strong { display: block; color: #263752; font-size: 12.5px; font-weight: 700; }
    .dash-check-copy span { display: block; color: #8490a4; font-size: 11px; margin-top: 1px; }
    .dash-check-action { margin-left: auto; flex: 0 0 auto; }
    .dash-check-action a {
        display: inline-flex; align-items: center; gap: 4px;
        color: #2855b1; font-size: 11px; font-weight: 800; text-decoration: none;
        padding: 5px 8px; border-radius: 6px; background: #eef4ff;
    }
    .dash-check-action a:hover { background: #e2ecff; }
    .dash-check-action .ok { color: #15803d; background: transparent; font-size: 11px; }
    .dash-complete-note {
        margin-top: 13px; padding: 9px 11px; border-radius: 8px;
        background: #effaf2; color: #197744; font-size: 11px; font-weight: 600;
    }
    @media (max-width: 900px) {
        .dash-status-grid { grid-template-columns: 1fr; }
        .dash-topbar { align-items: flex-start; flex-direction: column; }
    }
</style>

<div class="dash-status-wrap">
    <div class="dash-topbar">
        <div>
            <h2>Panel de control</h2>
            <p>Resumen de tu despacho y estado de la configuración mínima.</p>
        </div>
        <div class="dash-year">
            <label for="dash-year">Año</label>
            <select id="dash-year" wire:model="year">
                @foreach($listYears as $y)
                    <option value="{{ $y }}">{{ $y }}</option>
                @endforeach
            </select>
        </div>
    </div>

    <div class="dash-status-grid">
        <section class="dash-card dash-plan">
            <div class="dash-plan-head">
                <div>
                    <div class="dash-kicker">Plan actual</div>
                    <div class="dash-plan-name">{{ $planNombre }}</div>
                    <p class="dash-plan-sub">
                        {{ $esSaas ? 'Suscripción del despacho' : 'Instalación en servidor propio' }}
                    </p>
                </div>
                <div class="dash-plan-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M12 3v18M5 21h14M6 6l-3 6h6L6 6Zm12 0-3 6h6l-3-6ZM6 6h12"/>
                    </svg>
                </div>
            </div>

            <div style="margin-top:15px;">
                <span class="dash-status {{ $planEstado === 'Activo' ? 'active' : 'pending' }}">{{ $planEstado }}</span>
            </div>

            <div class="dash-plan-meta">
                @if($esSaas)
                    <div class="dash-plan-meta-row"><span>Monto</span><strong>${{ number_format((float) $planMonto, 2) }}</strong></div>
                    <div class="dash-plan-meta-row"><span>Vence</span><strong>{{ $planVencimiento ?: 'Sin fecha' }}</strong></div>
                    <div class="dash-plan-meta-row"><span>Dominio</span><strong>{{ $tenantDominio ?: '—' }}</strong></div>
                @else
                    <div class="dash-plan-meta-row"><span>Modalidad</span><strong>Local</strong></div>
                    <div class="dash-plan-meta-row"><span>Licencia</span><strong>Sin vencimiento</strong></div>
                    <div class="dash-plan-meta-row"><span>Servidor</span><strong>Propio</strong></div>
                @endif
            </div>
        </section>

        <section class="dash-card dash-checklist">
            <div class="dash-checklist-head">
                <div>
                    <div class="dash-kicker">Configuración mínima</div>
                    <h3>{{ $configCompleta ? 'Configuración básica OK' : 'Completa los datos esenciales' }}</h3>
                </div>
                <div class="dash-progress {{ $configCompleta ? 'complete' : '' }}">
                    <div class="dash-progress-track">
                        <div class="dash-progress-fill" style="width: {{ $configPorcentaje }}%"></div>
                    </div>
                    <span class="dash-progress-label">{{ $configCompletos }}/{{ $configTotal }}</span>
                </div>
            </div>

            @if(count($configItems))
            <ul class="dash-checklist-list">
                @foreach($configItems as $item)
                    <li class="dash-check-item">
                        <span class="dash-check-icon {{ $item['done'] ? 'done' : 'pending' }}">
                            @if($item['done'])
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg>
                            @else
                                !
                            @endif
                        </span>
                        <div class="dash-check-copy">
                            <strong>{{ $item['label'] }}</strong>
                            <span>{{ $item['description'] }}</span>
                        </div>
                        <div class="dash-check-action">
                            @if($item['done'])
                                <span class="ok">Listo</span>
                            @else
                                <a href="{{ $item['url'] }}">{{ $item['action'] }} →</a>
                            @endif
                        </div>
                    </li>
                @endforeach
            </ul>
            @endif

            @if($configCompleta)
                <div class="dash-complete-note">Configuración básica OK. Todos los datos esenciales están cargados.</div>
            @endif
        </section>
    </div>
</div>

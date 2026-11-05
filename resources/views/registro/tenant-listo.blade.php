<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Espacio creado · Adlatere SaaS</title>
    <style>
        :root { --brand:#173b99; --ink:#12213d; --muted:#66748b; --line:#e1e7f1; --green:#22c55e; }
        * { box-sizing: border-box; }
        body {
            margin: 0; min-height: 100vh; display: grid; place-items: center;
            background: radial-gradient(circle at 12% 18%, #e8f1ff, transparent 42%), #f5f8fd;
            font-family: "Inter", "Segoe UI", system-ui, sans-serif; color: var(--ink);
        }
        .card {
            width: min(620px, 92vw); background: #fff; border: 1px solid var(--line);
            border-radius: 20px; box-shadow: 0 24px 60px rgba(15, 33, 68, .13); overflow: hidden;
        }
        .head { padding: 28px 30px 24px; background: linear-gradient(135deg, #0d2450, #173b99); color: #fff; }
        .head small { display: block; color: #bfd0f3; font-size: 10px; letter-spacing: .13em; text-transform: uppercase; font-weight: 700; }
        .head h1 { margin: 9px 0 6px; font-size: 25px; letter-spacing: -.03em; line-height: 1.2; }
        .head p { margin: 0; color: #c8d8f3; font-size: 13px; }
        .body { padding: 26px 30px 30px; }
        .success { display:flex; align-items:center; gap: 10px; padding: 12px 14px; border-radius: 10px; background:#eaf9ee; color:#166534; font-size:13px; font-weight:700; margin-bottom:20px; }
        .success .check { width: 24px; height: 24px; border-radius: 50%; display:grid; place-items:center; background:#22c55e; color:#fff; }
        .row { display:grid; grid-template-columns: 150px 1fr; gap: 10px; padding: 9px 0; border-top: 1px solid #eef1f7; font-size: 13px; }
        .row .label { color:#78849a; }
        .row .value { font-weight: 600; }
        .host-box { background:#f4f8ff; border:1px solid #dfeafb; border-radius: 10px; padding: 14px; margin-top: 19px; font-size: 12px; color: #526483; }
        .host-box strong { color:#173b99; }
        .host-line { display:flex; gap:8px; align-items:center; margin-top:8px; }
        .host-line code { flex:1; background:#fff; border:1px solid #dce6f6; border-radius:7px; padding:8px 10px; color:#233c6b; overflow-wrap:anywhere; }
        .btn {
            display:inline-flex; align-items:center; justify-content:center; gap:7px;
            border:0; border-radius:9px; padding:10px 14px; font-size:13px; font-weight:700; text-decoration:none; cursor:pointer;
        }
        .btn-primary { background: var(--green); color:#06130a; }
        .btn-primary:hover { filter:brightness(1.04); }
        .btn-secondary { background:#eef2f9; color:#3c4c66; }
        .btn-secondary:hover { background:#e3e9f4; }
        .actions { display:flex; flex-wrap:wrap; gap:9px; margin-top:20px; }
        .note { margin:16px 0 0; color:#8a95a8; font-size:11px; line-height:1.6; }
        .footer { margin-top: 22px; padding-top: 15px; border-top: 1px solid #edf0f6; color:#8a95a8; font-size:11px; }
    </style>
</head>
<body>
    <div class="card">
        <div class="head">
            <small>Adlatere SaaS</small>
            <h1>Tu espacio fue creado correctamente</h1>
            <p>Ya puedes iniciar sesión con la cuenta administradora del despacho.</p>
        </div>
        <div class="body">
            <div class="success">
                <span class="check">✓</span>
                Base de datos, dominio y usuario administrador listos.
            </div>

            <div class="row"><div class="label">Despacho</div><div class="value">{{ $tenant->name }}</div></div>
            <div class="row"><div class="label">Dominio</div><div class="value">{{ $domain }}</div></div>
            <div class="row"><div class="label">Plan</div><div class="value">{{ $tenant->suscription_type }}</div></div>

            <div class="host-box">
                <strong>Un paso para abrir el subdominio en local</strong><br>
                En tu equipo de desarrollo, agrega esta línea al archivo <code>{{ $hostsPath }}</code>:
                <div class="host-line">
                    <code id="host-line">{{ $hostsEntry }}</code>
                    <button class="btn btn-secondary" type="button" onclick="copyHost()">Copiar</button>
                </div>
                También puedes descargar un script y ejecutarlo con clic derecho → <strong>Ejecutar como administrador</strong>.
            </div>

            <div class="actions">
                <a class="btn btn-primary" href="http://{{ $domain }}/login">Abrir login del despacho</a>
                <a class="btn btn-secondary" href="{{ $scriptUrl }}">Descargar script para hosts</a>
                <a class="btn btn-secondary" href="{{ url('/') }}">Volver al inicio</a>
            </div>

            <p class="note">
                Si ejecutaste el script, vuelve a abrir esta página o pulsa “Abrir login”. Si el navegador aún no encuentra el subdominio, cierra y abre el navegador o ejecuta <code>ipconfig /flushdns</code>.
            </p>

            <div class="footer">Este aviso aparece solo en entorno local cuando el dominio aún no está registrado en el archivo hosts.</div>
        </div>
    </div>

    <script>
        function copyHost() {
            const text = document.getElementById('host-line').innerText;
            if (navigator.clipboard) {
                navigator.clipboard.writeText(text);
            }
        }
    </script>
</body>
</html>

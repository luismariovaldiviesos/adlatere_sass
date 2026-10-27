<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Verificación de documento</title>
    <style>
        body { margin: 0; background: #f1f5f9; color: #0f172a; font-family: Arial, Helvetica, sans-serif; }
        .wrap { max-width: 720px; margin: 40px auto; padding: 0 16px; }
        .card { background: #fff; border: 1px solid #e2e8f0; border-radius: 14px; box-shadow: 0 10px 30px rgba(15,23,42,.08); overflow: hidden; }
        .head { padding: 24px; background: linear-gradient(135deg, #0f172a, #1e3a8a 55%, #2563eb); color: #fff; }
        .head h1 { margin: 0; font-size: 22px; }
        .head p { margin: 8px 0 0; color: #dbeafe; font-size: 14px; }
        .body { padding: 24px; }
        .ok { display: inline-flex; align-items: center; gap: 8px; background: #dcfce7; color: #166534; padding: 8px 12px; border-radius: 999px; font-weight: bold; font-size: 14px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        td { padding: 9px 0; border-bottom: 1px solid #e2e8f0; vertical-align: top; font-size: 14px; }
        td:first-child { width: 190px; color: #64748b; font-weight: bold; }
        .hash { word-break: break-all; font-family: monospace; font-size: 12px; }
        .foot { margin-top: 22px; color: #64748b; font-size: 12px; }
    </style>
</head>
<body>
<div class="wrap">
    <div class="card">
        <div class="head">
            <h1>Verificación de documento firmado</h1>
            <p>La información coincide con el documento registrado en el sistema.</p>
        </div>
        <div class="body">
            <span class="ok">✓ Firma válida</span>

            <table>
                <tr><td>Tipo de actividad</td><td>{{ $actividad->tipoActividad->nombre ?? 'Actividad' }}</td></tr>
                <tr><td>Juicio</td><td>{{ $actividad->juicio->cod_satje ?? $actividad->juicio_id }}</td></tr>
                <tr><td>Titular del certificado</td><td>{{ $actividad->firma_titular ?? '—' }}</td></tr>
                <tr><td>Tramitado por</td><td>{{ $actividad->firmadoPor->name ?? '—' }}</td></tr>
                <tr><td>Fecha de firma</td><td>{{ $actividad->firmado_en ? \Carbon\Carbon::parse($actividad->firmado_en)->format('d/m/Y H:i') : '—' }}</td></tr>
                <tr><td>Origen del certificado</td><td>{{ ($actividad->firma_origen ?? '') === 'despacho' ? 'Certificado del despacho' : 'Certificado personal' }}</td></tr>
                <tr><td>Emisor</td><td>{{ data_get($actividad->firma_metadatos, 'cert_issuer', '—') }}</td></tr>
                <tr><td>Serie</td><td>{{ data_get($actividad->firma_metadatos, 'cert_serial', '—') }}</td></tr>
                <tr><td>Huella SHA-256</td><td class="hash">{{ $hash }}</td></tr>
            </table>

            <p class="foot">Este documento fue firmado electrónicamente. La verificación muestra los datos registrados; no sustituye la validación técnica del archivo PDF.</p>
        </div>
    </div>
</div>
</body>
</html>

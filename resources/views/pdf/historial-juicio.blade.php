<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<style>
    @page { margin: 1.6cm 1.5cm 1.8cm 1.5cm; }
    body { font-family: 'DejaVu Sans', sans-serif; font-size: 8.5pt; line-height: 1.35; color: #111827; }
    .header { text-align: center; margin-bottom: 14px; }
    .logo { width: 100px; max-height: 55px; }
    .empresa { font-size: 12pt; font-weight: bold; color: #1e3a8a; }
    h1 { font-size: 15pt; margin: 8px 0 3px; color: #1e3a8a; text-transform: uppercase; }
    .subtitulo { font-size: 9pt; color: #475569; margin-bottom: 12px; }
    .meta { width: 100%; border-collapse: collapse; margin-bottom: 12px; }
    .meta td { padding: 3px 6px; vertical-align: top; }
    .meta .label { font-weight: bold; width: 150px; color: #334155; }
    .resumen { background: #eff6ff; border: 1px solid #bfdbfe; padding: 8px 10px; margin-bottom: 12px; }
    .resumen .estado { font-size: 11pt; font-weight: bold; color: #0c4a6e; text-transform: uppercase; }
    table.historial { width: 100%; border-collapse: collapse; }
    table.historial th { background: #1e3a8a; color: #fff; font-size: 7.5pt; text-transform: uppercase; padding: 5px 4px; text-align: left; }
    table.historial td { border-bottom: 1px solid #cbd5e1; padding: 5px 4px; vertical-align: top; }
    table.historial tr:nth-child(even) td { background: #f8fafc; }
    .fecha { white-space: nowrap; width: 82px; }
    .movimiento { width: 105px; font-weight: bold; color: #1e3a8a; text-transform: uppercase; }
    .usuario { width: 95px; }
    .descripcion { width: auto; }
    .footer { position: fixed; bottom: -10px; left: 0; right: 0; text-align: center; font-size: 7pt; color: #64748b; }
</style>
</head>
<body>
@php
    $empresa = \App\Models\Setting::first();
    $logoPath = ($empresa && $empresa->logo) ? public_path($empresa->logo) : null;
    $logoData = null;
    if ($logoPath && is_file($logoPath)) {
        $mime = function_exists('mime_content_type') ? (@mime_content_type($logoPath) ?: 'image/png') : 'image/png';
        $logoData = 'data:' . $mime . ';base64,' . base64_encode(file_get_contents($logoPath));
    }
@endphp

<div class="header">
    @if($logoData)
        <img src="{{ $logoData }}" class="logo" alt="Logo">
    @else
        <div class="empresa">{{ $empresa->nombreComercial ?? $empresa->razonSocial ?? 'Despacho jurídico' }}</div>
    @endif
    <h1>Historial del juicio</h1>
    <div class="subtitulo">{{ $juicio->cod_satje ?: 'Juicio #' . $juicio->id }}</div>
</div>

<table class="meta">
    <tr>
        <td class="label">Materia:</td>
        <td>{{ $juicio->asunto->procedimiento->materia->nombre ?? '—' }}</td>
        <td class="label">Procedimiento:</td>
        <td>{{ $juicio->asunto->procedimiento->nombre ?? '—' }}</td>
    </tr>
    <tr>
        <td class="label">Asunto:</td>
        <td>{{ $juicio->asunto->nombre ?? '—' }}</td>
        <td class="label">Unidad judicial:</td>
        <td>{{ $juicio->unidadJudicial->nombre ?? '—' }}</td>
    </tr>
    <tr>
        <td class="label">Actor(es):</td>
        <td>{{ $juicio->actores->pluck('businame')->filter()->implode(', ') ?: '—' }}</td>
        <td class="label">Demandado(s):</td>
        <td>{{ $juicio->demandados->pluck('businame')->filter()->implode(', ') ?: '—' }}</td>
    </tr>
</table>

<div class="resumen">
    <div>Estado procesal actual</div>
    <div class="estado">{{ $juicio->estadoProcesal->nombre ?? 'Sin estado procesal' }}</div>
    <div style="margin-top:4px;">Movimientos registrados: {{ $historial->count() }}</div>
</div>

<table class="historial">
    <thead>
        <tr>
            <th class="fecha">Fecha</th>
            <th class="movimiento">Movimiento</th>
            <th class="usuario">Usuario</th>
            <th class="descripcion">Detalle</th>
        </tr>
    </thead>
    <tbody>
        @forelse($historial as $hito)
        <tr>
            <td class="fecha">{{ \Carbon\Carbon::parse($hito->created_at)->format('d/m/Y H:i') }}</td>
            <td class="movimiento">{{ ucfirst(str_replace('_', ' ', $hito->tipo_movimiento)) }}</td>
            <td class="usuario">{{ $hito->user->name ?? 'Sistema' }}</td>
            <td class="descripcion">{{ $hito->descripcion }}</td>
        </tr>
        @empty
        <tr>
            <td colspan="4">No hay movimientos registrados.</td>
        </tr>
        @endforelse
    </tbody>
</table>

<div class="footer">
    Generado el {{ $generadoEn->format('d/m/Y H:i') }} · {{ $empresa->nombreComercial ?? $empresa->razonSocial ?? 'Sistema jurídico' }}
</div>
</body>
</html>

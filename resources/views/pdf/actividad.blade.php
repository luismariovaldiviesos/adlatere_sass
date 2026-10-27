<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<style>
@page { margin: 2cm 2cm 2.2cm 2cm; }
body { font-family: 'DejaVu Sans', sans-serif; font-size: 10.5pt; line-height: 1.5; color: #111; }
.header { text-align: center; margin-bottom: 18px; }
.company-logo { width: 120px; max-height: 60px; }
.company-name { font-size: 13pt; font-weight: bold; color: #1e3a8a; }
.activity-type { margin: 10px 0 0; font-size: 16pt; font-weight: bold; text-transform: uppercase; letter-spacing: .4px; }
.meta { background: #f8f9fa; border: 1px solid #dee2e6; border-radius: 4px; padding: 15px; margin-bottom: 20px; }
.meta table { width: 100%; border-collapse: collapse; }
.meta td { padding: 4px 8px; vertical-align: top; }
.meta .label { font-weight: bold; width: 180px; color: #333; }
.contenido { min-height: 200px; }
.footer { position: fixed; bottom: 0; left: 0; right: 0; font-size: 7pt; color: #666; text-align: center; }
</style>
</head>
<body>
@php
    $empresa = $empresa ?? \App\Models\Setting::first();
    $logoPath = ($empresa && $empresa->logo) ? public_path($empresa->logo) : null;
    $logoData = null;

    if ($logoPath && is_file($logoPath)) {
        $mime = function_exists('mime_content_type') ? (@mime_content_type($logoPath) ?: 'image/png') : 'image/png';
        $logoData = 'data:' . $mime . ';base64,' . base64_encode(file_get_contents($logoPath));
    }

    $nombreDespacho = $empresa->nombreComercial ?? $empresa->razonSocial ?? null;
@endphp
<div class="header">
    @if($logoData)
        <img src="{{ $logoData }}" class="company-logo" alt="Logo">
    @else
        <div class="company-name">{{ $nombreDespacho ?: 'Sin logo' }}</div>
    @endif
    <h1 class="activity-type">{{ $actividad->tipoActividad->nombre ?? 'Actividad' }}</h1>
</div>
<div class="meta">
<table>
<tr><td class="label">Juicio:</td><td>{{ $actividad->juicio->cod_satje ?? $actividad->juicio_id }}</td></tr>
<tr><td class="label">Materia:</td><td>{{ $actividad->juicio->asunto->procedimiento->materia->nombre ?? '—' }}</td></tr>
<tr><td class="label">Juzgado:</td><td>{{ $actividad->juicio->unidadJudicial->nombre ?? '—' }}</td></tr>
<tr><td class="label">Fecha/Hora:</td><td>{{ \Carbon\Carbon::parse($actividad->fecha_actividad)->format('d/m/Y H:i') }}</td></tr>
<tr><td class="label">Abogado:</td><td>{{ $actividad->user->name ?? '—' }} (CI: {{ $actividad->user->ci ?? '—' }})</td></tr>
<tr><td class="label">Actores:</td><td>{{ $actividad->juicio->actores->pluck('businame')->implode(', ') ?: '—' }}</td></tr>
<tr><td class="label">Demandados:</td><td>{{ $actividad->juicio->demandados->pluck('businame')->implode(', ') ?: '—' }}</td></tr>
</table>
</div>
<div class="contenido">
{!! $actividad->contenido !!}
</div>
<div class="footer">
Elaborado por: {{ $actividad->user->name ?? '—' }} CI: {{ $actividad->user->ci ?? '—' }} | Fecha de elaboración: {{ now()->format('d/m/Y H:i') }}
</div>
</body>
</html>

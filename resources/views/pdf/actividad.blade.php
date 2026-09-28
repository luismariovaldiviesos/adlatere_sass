<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<style>
@page { margin: 2cm; }
body { font-family: 'DejaVu Sans', sans-serif; font-size: 11pt; line-height: 1.5; color: #111; }
.header { text-align: center; border-bottom: 2px solid #333; padding-bottom: 10px; margin-bottom: 20px; }
.header h2 { margin: 0; font-size: 18pt; }
.header small { color: #666; }
.meta { background: #f8f9fa; border: 1px solid #dee2e6; border-radius: 4px; padding: 15px; margin-bottom: 20px; }
.meta table { width: 100%; border-collapse: collapse; }
.meta td { padding: 4px 8px; vertical-align: top; }
.meta .label { font-weight: bold; width: 180px; color: #333; }
.contenido { border: 1px solid #ccc; border-radius: 4px; padding: 20px; min-height: 200px; }
.footer { margin-top: 30px; padding-top: 15px; border-top: 1px solid #999; font-size: 9pt; color: #666; }
</style>
</head>
<body>
<div class="header">
    <h2>ACTUACIÓN PROCESAL</h2>
    <small>{{ $actividad->tipoActividad->nombre ?? 'Actividad' }}</small>
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
<p><strong>Firmado digitalmente por:</strong> {{ $actividad->user->name ?? '—' }}</p>
<p>CI: {{ $actividad->user->ci ?? '—' }} | Fecha: {{ now()->format('d/m/Y H:i') }}</p>
<p>Este documento cuenta con firma electrónica avanzada según normativa vigente.</p>
</div>
</body>
</html>
<?php

namespace App\Http\Controllers;

use App\Models\Actividad;
use Illuminate\Support\Facades\Storage;

class ActividadPdfController extends Controller
{
    public function firmado(Actividad $actividad)
    {
        $user = auth()->user();
        abort_unless($user, 403);

        $esAdmin = ($user->profile ?? null) === 'Admin'
            || (method_exists($user, 'hasRole') && $user->hasRole('Admin'));

        $veJuicio = $esAdmin || \App\Models\Juicio::where('id', $actividad->juicio_id)
            ->whereHas('abogados', fn($q) => $q->where('user_id', $user->id))
            ->exists();

        abort_unless($veJuicio, 403);
        abort_unless($actividad->pdf_firmado_path, 404);

        $path = Storage::disk('public')->path('actividades/firmados/' . $actividad->pdf_firmado_path);
        abort_unless(is_file($path), 404);

        return response()->file($path, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="' . $actividad->nombrePdf() . '"',
        ]);
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\Actividad;
use Illuminate\Http\Request;

class VerificacionActividadController extends Controller
{
    public function show(Request $request, $actividad)
    {
        $hash = (string) $request->query('hash', '');

        $actividad = Actividad::with(['tipoActividad', 'juicio', 'firmadoPor'])
            ->where('id', $actividad)
            ->where('estado_firma', 'firmada')
            ->first();

        if (!$actividad || $hash === '' || !$actividad->firma_hash || !hash_equals($actividad->firma_hash, $hash)) {
            abort(404);
        }

        return view('verificacion.actividad', [
            'actividad' => $actividad,
            'hash' => $hash,
        ]);
    }
}

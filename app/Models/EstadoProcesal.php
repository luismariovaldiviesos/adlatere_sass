<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\Rule;

class EstadoProcesal extends Model
{
    use HasFactory;
     protected $fillable = ['nombre', 'fase_id', 'descripcion'];
     protected $table = 'estados_procesales';

    public static function rules($id, $faseId = null)
    {
        $nombreUnico = Rule::unique('estados_procesales', 'nombre')
            ->where(fn ($query) => $query->where('fase_id', $faseId));

        if ($id > 0) {
            $nombreUnico->ignore($id);
        }

        return [
            'nombre' => ['required', 'min:3', 'max:50', $nombreUnico],
            'fase_id' => 'required|exists:fase,id',
        ];
    }


    public static $messages = [
        'nombre.required' => 'Nombre requerido',
        'nombre.min' => 'El nombre debe tener al menos 3 caracteres',
        'nombre.max' => 'El nombre debe tener máximo 50 caracteres',
        'nombre.unique' => 'Ya existe un estado con ese nombre en la fase seleccionada.',
        'fase_id.required' => 'Fase requerida',
        'fase_id.exists' => 'La fase seleccionada no es válida.',
        
    ];


    public function fase()
    {
        return $this->belongsTo(Fase::class);
    }

    public function juicios()
    {
        return $this->hasMany(Juicio::class);
    }
}

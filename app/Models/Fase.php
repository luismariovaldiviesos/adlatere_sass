<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Fase extends Model
{
    use HasFactory;
    protected $fillable = ['nombre'];
    protected $table = 'fase';

    public static function rules($id)
    {
        if ($id <= 0) {
            return [
                'nombre' => 'required|min:3|max:50|unique:fase'
            ];
        } else {
            return [
                'nombre' => "required|min:3|max:50|unique:fase,nombre,{$id}"
            ];
        }
    }


    public static $messages = [
        'nombre.required' => 'Nombre requerido',
        'nombre.min' => 'El nombre debe tener al menos 3 caracteres',
        'nombre.max' => 'El nombre debe tener máximo 50 caracteres',
        'nombre.unique' => 'La fase procesal ya existe en sistema'
    ];


    public function estados_procesales()
    {
        return $this->hasMany(EstadoProcesal::class);
    }
}

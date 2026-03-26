<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Unidad extends Model
{
    use HasFactory;

    protected $fillable = ['nombre','canton_id'];

    public static function rules($id)
    {
        if ($id <= 0) {
            return [
                'nombre' => 'required|unique:unidads',
                'canton_id' => 'required'
            ];
        } else {
            return [
                'nombre' => "required|unique:unidads,nombre,{$id}",
                'canton_id' => 'required'
            ];
        }
    }

    public static $messages = [
        'nombre.required' => 'Nombre unidad requerido',
        'nombre.unique' => ' Unidad ya existe en sistema',
        'canton_id.required' => 'Cantón requerido',
    ];


public function canton()
    {
        return $this->belongsTo(Canton::class);
    }

     public function materias()
    {
        return $this->hasMany(Materia::class);
    }

   

   
   
}

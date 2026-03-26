<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Canton extends Model
{
    use HasFactory;
    protected $table = 'cantones';

    protected $fillable = [
        'nombre',
        'provincia_id',
    ];


     public static function rules($id)
    {
        if ($id <= 0) {
            return [
                'nombre' => 'required|unique:cantones',
                'provincia_id' => 'required'
            ];
        } else {
            return [
                'nombre' => "required|unique:cantones,nombre,{$id}",
                'provincia_id' => 'required'
            ];
        }
    }

    public static $messages = [
        'nombre.required' => 'Nombre cantón requerido',
        'nombre.unique' => 'La provincia ya existe en sistema',
        'provincia_id.required' => 'provincia cantón requerido',
    ];


    public function provincia()
    {
        return $this->belongsTo(Provincia::class);
    }

     public function unidades()
    {
        return $this->hasMany(Unidad::class);
    }
}

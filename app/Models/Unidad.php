<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\Rule;

class Unidad extends Model
{
    use HasFactory;

    protected $fillable = ['nombre','canton_id'];

    public static function rules($id, $cantonId = null)
    {
        $nombreUnico = Rule::unique('unidads', 'nombre')
            ->where(fn ($query) => $query->where('canton_id', $cantonId));

        if ($id > 0) {
            $nombreUnico->ignore($id);
        }

        return [
            'nombre' => ['required', $nombreUnico],
            'canton_id' => 'required|exists:cantones,id',
        ];
    }

    public static $messages = [
        'nombre.required' => 'Nombre unidad requerido',
        'nombre.unique' => 'Ya existe una unidad con ese nombre en el cantón seleccionado.',
        'canton_id.required' => 'Cantón requerido',
    ];


public function canton()
    {
        return $this->belongsTo(Canton::class);
    }

     public function juicios()
    {
        return $this->hasMany(Juicio::class, 'unidad_id');
    }

    public function funcionarios()
    {
        return $this->belongsToMany(Funcionario::class, 'funcionario_unidad');
    }

    public function materias()
    {
        return $this->belongsToMany(Materia::class, 'materia_unidad')->withTimestamps();
    }

   

   
   
}

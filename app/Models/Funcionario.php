<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Funcionario extends Model
{
    use HasFactory;

    protected $fillable = [
        'nombre',
        'cargo',
        'telefono',
        'email'
    ];

    public static function rules($id)
    {
        if ($id <= 0) {
            return [
                'nombre' => 'required|min:3|max:50|unique:funcionarios',
                'cargo' => 'required|in:Juez,Secretario,Ayudante,Citador,Otro',
                'telefono' => 'nullable|string|max:20',
                'email' => 'nullable|email|max:255|unique:funcionarios,email'
            ];
        } else {
            return [
                'nombre' => "required|min:3|max:50|unique:funcionarios,nombre,{$id}",
                'cargo' => 'required|in:Juez,Secretario,Ayudante,Citador,Otro',
                'telefono' => 'nullable|string|max:20',
                'email' => "nullable|email|max:255|unique:funcionarios,email,{$id}"
            ];
        }
    }

    public static $messages = [
        'nombre.required' => 'Nombre requerido',
        'nombre.min' => 'El nombre debe tener al menos 3 caracteres',
        'nombre.max' => 'El nombre debe tener máximo 50 caracteres',
        'nombre.unique' => 'El funcionario ya existe en sistema',
        'cargo.required' => 'Cargo requerido',
        'cargo.in' => 'Cargo debe ser uno de los siguientes: Juez, Secretario, Ayudante, Citador, Otro',
        'telefono.string' => 'El teléfono debe ser una cadena de texto',
        'telefono.max' => 'El teléfono debe tener máximo 20 caracteres',
        'email.email' => 'El correo electrónico debe ser una dirección de correo válida',
        'email.max' => 'El correo electrónico debe tener máximo 255 caracteres',
        'email.unique' => 'El correo electrónico ya existe en sistema'
    ];


}

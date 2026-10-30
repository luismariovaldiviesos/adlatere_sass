<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    use HasFactory;
    protected $fillable = [
        'businame', 'typeidenti', 'valueidenti', 'address', 'estado_civil',
        'profesion_ocupacion', 'canton_id', 'email', 'phone', 'notes',
    ];




    //relaciones
    public function facturas()
    {
        return $this->hasMany(Factura::class, 'customer_id');
    }

    public static function rules($id){ // stattic para acceder sin tener que instanciar la clase

        if($id <=0 ){
            return [
                'businame' => 'required|min:3|unique:customers',
                'typeidenti' => 'required',
                'valueidenti' => 'required|max:13|unique:customers',
                'address' => 'required',
                'estado_civil' => 'nullable|string|max:50',
                'profesion_ocupacion' => 'nullable|string|max:150',
                'canton_id' => 'nullable|exists:cantones,id',
                'email' => 'required|unique:customers',
                'phone' => 'required'
            ];
        }

        else{
            return [
                'businame' => "required|min:3|string|unique:customers,businame,{$id}",
                'typeidenti' => 'required',
                'valueidenti' => "required|max:13|unique:customers,valueidenti,{$id}",
                'address' => 'required',
                'estado_civil' => 'nullable|string|max:50',
                'profesion_ocupacion' => 'nullable|string|max:150',
                'canton_id' => 'nullable|exists:cantones,id',
                'email' => "required|unique:customers,email,{$id}",
                'phone' => 'required'
            ];

        }
    }

    public static $messages =[
        'businame.required' => 'nombre requerido',
        'businame.min' => 'nombre debe tener al menos 3 caracteres',
        'businame.unique' => 'nombre ya esta en uso',
        'typeidenti.required' => 'tipo requerido',
        'valueidenti.required' => 'valor requerido',
        'valueidenti.max' => 'valor debe tener maximo 13 caracteres',
        'valueidenti.unique' => 'valor ya esta en uso',
        'address.required' => 'direccion es requerida',
        'email.required' => 'email es requerido',
        'email.unique' => 'email ya esta en uso',
        'phone.required' => 'email es requerido'
    ];


    public function canton()
    {
        return $this->belongsTo(Canton::class, 'canton_id');
    }

    public function juicios (){
        return $this->belongsToMany(Juicio::class, 'juicio_participante')->withPivot('rol', 'es_cliente');
    }


}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;

    protected $fillable = ['name'];

    public static function rules($id)
    {
        if ($id <= 0) {
            return [
                'name' => 'required|min:3|max:50|unique:categories'
            ];
        } else {
            return [
                'name' => "required|min:3|max:50|unique:categories,name,{$id}"
            ];
        }
    }

    public static $messages = [
        'name.required' => 'Nombre requerido',
        'name.min' => 'El nombre debe tener al menos 3 caracteres',
        'name.max' => 'El nombre debe tener máximo 50 caracteres',
        'name.unique' => 'La categoría ya existe en sistema'
    ];

    // relationships
    public function products()
    {
        return $this->hasMany(Product::class);
    }
    public function image()
    {
        //Este patrón a menudo se denomina patrón de objeto nulo y puede ayudar a eliminar las comprobaciones condicionales en su código.
        return  $this->morphOne(Image::class, 'model')->withDefault();
    }


    // accessors && mutators
    public function getImgAttribute()
    {
        $img = $this->image->file;

        if ($img != null) {
            // Use tenant-aware check
            if (\Illuminate\Support\Facades\Storage::disk('public')->exists('categories/' . $img)) {
                 // Use the anti-nginx route
                 return route('tenant.media', ['path' => 'categories/' . $img]);
            }
        }
        
        // Return global asset for 'no image'
        return global_asset('assets/img/noimg.svg'); 
    }
}

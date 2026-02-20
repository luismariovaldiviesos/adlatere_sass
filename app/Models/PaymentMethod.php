<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentMethod extends Model
{
    use HasFactory;

    protected $fillable = ['code', 'description', 'is_active'];

    public static function active()
    {
        return self::where('is_active', true)->get();
    }

    public function getCodeAndDescriptionAttribute()
    {
        return "{$this->code} - {$this->description}"; 
    }
}

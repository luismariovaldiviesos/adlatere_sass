<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Plan extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'description', 'price', 'invoice_limit','remote_plan_id'];

    protected $table = 'plans';

    protected $connection = 'mysql'; // Ensure central DB is used
}

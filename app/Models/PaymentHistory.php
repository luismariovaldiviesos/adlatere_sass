<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentHistory extends Model
{
    use HasFactory;

    protected $fillable = ['tenant_id', 'amount', 'stripe_id', 'payment_data'];

    protected $casts = [
        'payment_data' => 'array',
    ];

    protected $connection = 'mysql'; // Ensure central DB is used

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }
}

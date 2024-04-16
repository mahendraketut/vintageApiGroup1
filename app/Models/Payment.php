<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = ['method_id', 'order_id' ,'amount'];

    public function method(): BelongsTo
    {
        return $this->belongsTo(Payment_Method::class);
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo('App/Models/Order');
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SupplierPayment extends Model
{
    protected $fillable = [
        'amount',
        'purchase_id',
        'user_id',
    ];

    public function purchase()
    {
        return $this->belongsTo(\App\Models\Purchase::class, 'purchase_id');
    }
}

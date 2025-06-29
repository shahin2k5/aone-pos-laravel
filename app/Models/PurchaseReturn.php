<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PurchaseReturn extends Model
{
    protected $fillable = [
        'supplier_id',
        'purchase_id',
        'total_qnty',
        'total_amount',
        'return_amount',
        'profit_amount',
        'user_id',
        'notes',
    ];

    public function items()
    {
        return $this->hasMany(PurchaseReturnItems::class,'purchase_return_id');
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class,'supplier_id');
    }

    public function getCustomerName()
    {
        if ($this->customer) {
            return $this->customer->first_name . ' ' . $this->customer->last_name;
        }
        return __('customer.working');
    }
}

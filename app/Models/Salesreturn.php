<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Salesreturn extends Model
{
    protected $fillable = [
        'customer_id',
        'order_id',
        'total_qnty',
        'total_amount',
        'return_amount',
        'profit_amount',
        'user_id',
        'notes',
    ];

    public function items()
    {
        return $this->hasMany(SalesreturnItems::class,'salesreturn_id');
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class,'customer_id');
    }

    public function getCustomerName()
    {
        if ($this->customer) {
            return $this->customer->first_name . ' ' . $this->customer->last_name;
        }
        return __('customer.working');
    }
}

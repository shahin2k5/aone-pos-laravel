<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SalesreturnItemCart extends Model
{
   protected $fillable =[
        'purchase_price',
        'sell_price',
        'qnty',
        'total_price',
        'product_id',
        'order_id',
        'customer_id',
        'user_id'
 
    ];


    public function customer()
    {
        return $this->belongsTo(Customer::class,'customer_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}

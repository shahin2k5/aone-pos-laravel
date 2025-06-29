<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SalesreturnItems extends Model
{
    protected $fillable =[
        'salesreturn_id',
        'order_id',
        'product_id',
        'purchase_price',
        'sell_price',
        'qnty',
        'customer_id',
        'user_id',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class,'product_id');
    }
}

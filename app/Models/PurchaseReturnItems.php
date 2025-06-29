<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PurchaseReturnItems extends Model
{
    protected $fillable =[
        'purchase_return_id',
        'purchase_id',
        'product_id',
        'purchase_price',
        'sell_price',
        'qnty',
        'supplier_id',
        'user_id',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class,'product_id');
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PurchaseReturnItemCart extends Model
{
   protected $fillable =[
        'purchase_id',
        'product_id',
        'purchase_price',
        'sell_price',
        'qnty',
        'total_price',
        'supplier_id',
        'user_id'
 
    ];


    public function supplier()
    {
        return $this->belongsTo(Supplier::class,'supplier_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}

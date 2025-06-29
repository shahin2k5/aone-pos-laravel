<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PurchaseCart extends Model
{
    protected $fillable = [
        'user_id',
        'product_id',
        'supplier_id',
        'supplier_invoice_id',
        'qnty',
        'purchase_price',
        'sell_price',
    ];


    public function balance(){
        return $this->belongsTo(Supplier::class,'supplier_id');
    }
}

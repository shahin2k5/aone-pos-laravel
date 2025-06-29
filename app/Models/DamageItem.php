<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DamageItem extends Model
{
    protected $fillable =[

    
        'product_id',
        'purchase_price',
        'sell_price',
        'qnty',
        'total_price',
        'user_id',
        'notes',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class,'product_id');
    }
}

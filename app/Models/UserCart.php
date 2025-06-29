<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserCart extends Model
{
    protected $fillable = [
        'user_id',
        'product_id',
        'customer_id',
    ];


    public function balance(){
        return $this->belongsTo(Customer::class,'customer_id');
    }
}

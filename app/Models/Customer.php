<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Customer extends Model
{
    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'phone',
        'address',
        'avatar',
        'customer',
        'user_id',
        'balance',
    ];

    public function getAvatarUrl()
    {
        return Storage::url($this->avatar);
    }

    public function orders(){
        return $this->hasMany(Order::class, 'customer_id')->with('items');
    }

    public function orderLists()
    {
        return $this->hasManyThrough(OrderItem::class, Order::class);
    }

    public function payments(){
        return $this->hasManyThrough(Payment::class, Order::class);
    }


}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Product extends Model
{
    protected $fillable = [
        'name',
        'description',
        'image',
        'barcode',
        'purchase_price',
        'sell_price',
        'quantity',
        'status'
    ];

    public function getImageUrl()
    {
        if ($this->image) {
            return Storage::url($this->image);
        }
        return asset('images/img-placeholder.jpg');
    }
}

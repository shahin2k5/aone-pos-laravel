<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;

use Illuminate\Database\Eloquent\Scope;

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
        'status',
        'user_id',
        'branch_id',
        'company_id',
    ];

    protected $appends = ['image_url'];

    protected static function booted()
    {
        static::addGlobalScope('branch', function (Builder $builder) {
            if (Auth::check()) {
                $company_id = Auth::user()->company_id;
                $builder->where('products.company_id', $company_id);
            }
        });
    }

    public function getImageUrl()
    {
        if ($this->image) {
            return Storage::url($this->image);
        }
        return asset('images/img-placeholder.jpg');
    }

    public function getImageUrlAttribute()
    {
        return $this->getImageUrl();
    }
}

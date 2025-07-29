<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\ValidationException;

class BranchProductStock extends Model
{
    protected $table = 'branch_product_stock';
    protected $fillable = [
        'product_id',
        'branch_id',
        'quantity',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    protected static function booted()
    {
        static::saving(function ($model) {
            if ($model->quantity < 0) {
                throw ValidationException::withMessages([
                    'quantity' => 'Stock quantity cannot be negative.'
                ]);
            }
        });
    }
}

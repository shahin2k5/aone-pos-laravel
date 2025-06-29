<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Purchase extends Model
{
     protected $fillable = [
        'supplier_id',
        'user_id',
        'invoice_no',
        'sub_total',
        'discount_amount',
        'gr_total',
        'paid_amount',
    ];


    public function supplierPayments()
    {
        return $this->hasMany(SupplierPayment::class);
    }

    public function items()
    {
        return $this->hasMany(PurchaseItem::class);
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class,'supplier_id');
    }
}

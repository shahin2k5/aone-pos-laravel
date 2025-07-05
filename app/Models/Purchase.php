<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class Purchase extends Model
{
     protected $fillable = [
        'supplier_id',
        'invoice_no',
        'sub_total',
        'discount_amount',
        'gr_total',
        'paid_amount',
        'user_id',
        'branch_id',
        'company_id',
    ];

    protected static function booted() {
        static::addGlobalScope('branch', function (Builder $builder) {
             $user = auth()->user();
            $company_id = $user->company_id;
            $branch_id = $user->branch_id;
            $role = $user->role;
            if($role=="admin"){
                $builder->where('company_id', $company_id);
            }else{
                $builder->where('company_id', $company_id)->where('branch_id', $branch_id);
            }
        });
    }

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

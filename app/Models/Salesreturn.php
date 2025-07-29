<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class Salesreturn extends Model
{
    protected $fillable = [
        'customer_id',
        'order_id',
        'total_qnty',
        'total_amount',
        'return_amount',
        'profit_amount',
        'user_id',
        'notes',
        'branch_id',
        'company_id',
    ];

    protected static function booted()
    {
        static::addGlobalScope('branch', function (Builder $builder) {
            $user = Auth::user();
            $company_id = $user->company_id;
            $branch_id = $user->branch_id;
            $role = $user->role;
            if ($role == "admin") {
                $builder->where('company_id', $company_id);
            } else {
                $builder->where('company_id', $company_id)->where('branch_id', $branch_id);
            }
        });
    }

    public function items()
    {
        return $this->hasMany(SalesreturnItems::class, 'salesreturn_id');
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    public function getCustomerName()
    {
        if ($this->customer) {
            return $this->customer->first_name . ' ' . $this->customer->last_name;
        }
        return __('customer.working');
    }

    public function getFormattedLoss()
    {
        $amount = abs($this->profit_amount);
        $type = $this->profit_amount < 0 ? 'Loss' : 'Gain';
        return number_format($amount, 2) . ' (' . $type . ')';
    }

    public function getLossClass()
    {
        return $this->profit_amount < 0 ? 'text-danger' : 'text-success';
    }
}

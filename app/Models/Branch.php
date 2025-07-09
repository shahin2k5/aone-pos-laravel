<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class Branch extends Model
{
    protected $fillable = [
        'branch_name',
        'address',
        'mobile',
        'admin_id',
        'branch_id',
        'company_id',
    ];
    
    protected static function booted() {
        static::addGlobalScope('branch', function (Builder $builder) {
            $company_id = auth()->user()->company_id;
            $builder->where('company_id', $company_id);
        });
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class Company extends Model
{
    protected $fillable = [
        'company_name',
        'company_type',
        'total_branch',
        'address',
        'mobile',
        'admin_id',
    ];

    protected static function booted() {
        static::addGlobalScope('branch', function (Builder $builder) {
            $user_id = auth()->user()->id;
            $builder->where('user_id', $user_id);
        });
    }
}

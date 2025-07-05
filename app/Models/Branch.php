<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Branch extends Model
{
    protected $fillable = [
        'branch_name',
        'address',
        'mobile',
        'user_id',
        'company_id'
    ];
}

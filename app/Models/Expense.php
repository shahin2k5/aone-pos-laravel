<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Expense extends Model
{
    protected $fillable =[

    
        'expense_head',
        'expense_description',
        'expense_amount',
        'user_id',
 
    ];

    
}

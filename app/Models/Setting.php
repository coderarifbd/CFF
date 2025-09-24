<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Setting extends Model
{
    use HasFactory;

    protected $fillable = [
        'monthly_subscription_amount',
        'fine_amount',
        'allow_accountant_edit_deposits',
        'allow_accountant_edit_expenses',
        'allow_accountant_edit_other_income',
        'allow_accountant_edit_investment_interest',
    ];
}

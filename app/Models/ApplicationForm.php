<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ApplicationForm extends Model
{
    use HasFactory;

    protected $fillable = [
        'first_name',
        'last_name',
        'middle_name',
        'gender',
        'status',
        'educ_attain',
        'residence',
        'amortization',
        'rent',
        'sss',
        'tin',
        'income',
        'superior',
        'employment_status',
        'yrs_in_service',
        'rate',
        'employer',
        'salary',
        'business',
        'living_exp',
        'rental_exp',
        'education_exp',
        'transportation',
        'insurance',
        'bills',
        'spouse_name',
        'b_date',
        'spouse_work',
        'children_num',
        'children_dep',
        'school'
    ];
}

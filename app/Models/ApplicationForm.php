<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Address;

class ApplicationForm extends Model
{
    use HasFactory;

    protected $fillable = [
        'address_id',
        'record_id',
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

    public function address() {
        return $this->belongsTo(Address::class);
    }

    public function user() {
        return $this->belongsTo(User::class);
    }
}

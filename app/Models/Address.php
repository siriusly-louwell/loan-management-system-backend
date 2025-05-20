<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Address extends Model
{
    use HasFactory;

    protected $fillable = [
        'personal_pres',
        'personal_prev',
        'parent_pres',
        'parent_prev',
        'spouse_prev',
        'spuse_prev',
        'employer_address'
    ];
}

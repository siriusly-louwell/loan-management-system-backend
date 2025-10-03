<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\ApplicationForm;

class Address extends Model
{
    use HasFactory;

    protected $guarded = ['id'];
    // protected $fillable = [
    //     'personal_pres',
    //     'personal_prev',
    //     'parent_pres',
    //     'parent_prev',
    //     'spouse_pres',
    //     'spouse_prev',
    //     'employer_address'
    // ];

    public function applicationForm()
    {
        return $this->hasOne(ApplicationForm::class);
    }
}

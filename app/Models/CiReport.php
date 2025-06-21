<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\ApplicationForm;

class CiReport extends Model
{
    use HasFactory;

    protected $fillable = [
        'birth_day', 'birth_place', 'application_form_id', 'father_first', 'father_middle', 'father_last', 'mother_first', 'mother_middle', 'mother_last',
        'comm_standing', 'home_description', 'recommendation', 'remarks', 'first_unit', 'sketch', 'delivered', 'outlet'
    ];

    public function application_form() {
        return $this->belongsTo(ApplicationForm::class);
    }
}

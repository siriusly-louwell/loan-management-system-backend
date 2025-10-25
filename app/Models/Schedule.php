<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\ApplicationForm;
use App\Models\Payment;

class Schedule extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    public function application()
    {
        return $this->belongsTo(ApplicationForm::class, 'application_form_id');
    }

    public function application_form()
    {
        return $this->belongsTo(ApplicationForm::class);
    }
}

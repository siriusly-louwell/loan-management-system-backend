<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\ApplicationForm;

class Payment extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    public function application()
    {
        return $this->belongsTo(ApplicationForm::class, 'application_form_id');
    }

    public function schedules()
    {
        return $this->hasMany(Schedule::class);
    }
}

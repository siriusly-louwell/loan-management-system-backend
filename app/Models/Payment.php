<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\ApplicationForm;
use App\Models\User;
use App\Models\Schedule;

class Payment extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    public function application()
    {
        return $this->belongsTo(ApplicationForm::class, 'application_form_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function schedules()
    {
        return $this->hasMany(Schedule::class);
    }
}

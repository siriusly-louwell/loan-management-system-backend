<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Comaker extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    public function application()
    {
        return $this->belongsTo(ApplicationForm::class, 'application_form_id');
    }
}

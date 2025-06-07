<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\ApplicationForm;
use App\Models\Motorcycle;

class Transaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'application_id', 'motorcycle_id', 'color', 'tenure', 'downpayment', 'quantity'
    ];

    public function application() {
        return $this->belongsTo(ApplicationForm::class);
    }

    public function motorcycle() {
        return $this->belongsTo(Motorcycle::class);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\ApplicationForm;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'application_form_id', 'cert_num', 'issued_at', 'prev_balance', 'curr_balance', 'amount_paid', 'status'
    ];

    public function application() {
        return $this->belongsTo(ApplicationForm::class);
    }
}

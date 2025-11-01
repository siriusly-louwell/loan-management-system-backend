<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use App\Models\Address;
use App\Models\User;
use App\Models\CiReport;
use App\Models\Transaction;
use App\Models\Payment;
use App\Models\CreditHistory;
use App\Models\Schedule;
use App\Models\Comaker;

class ApplicationForm extends Model
{
    use HasFactory;
    use Notifiable;

    protected $guarded = ['id'];

    public function address() {
        return $this->belongsTo(Address::class);
    }

    public function user() {
        return $this->belongsTo(User::class);
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

    public function credits()
    {
        return $this->hasMany(CreditHistory::class);
    }

    public function payment()
    {
        return $this->hasMany(Payment::class, 'application_form_id');
    }

    public function schedules()
    {
        return $this->hasMany(Schedule::class, 'application_form_id');
    }

    public function ciReport() {
        return $this->hasOne(CiReport::class);
    }

    public function comaker() {
        return $this->hasOne(Comaker::class);
    }
}

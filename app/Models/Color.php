<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Motorcycle;

class Color extends Model
{
    use HasFactory;

    protected $fillable = ['hex_value', 'motorcycle_id', 'quantity'];

    public function motorcycle()
    {
        return $this->belongsTo(Motorcycle::class);
    }
    public function images()
    {
        return $this->hasMany(Image::class);
    }
}

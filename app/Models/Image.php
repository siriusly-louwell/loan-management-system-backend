<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Motorcycle;

class Image extends Model
{
    use HasFactory;

    protected $fillable = ['path', 'motorcycle_id'];
    
    public function motorcyle()
    {
        return $this->belongsTo(Motorcycle::class);
    }
}

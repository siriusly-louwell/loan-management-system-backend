<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Motorcycle extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'brand', 'color', 'description', 'price', 'quantity', 'file_path', 'interest', 'rebate', 'tenure'];

    public function colors()
    {
        return $this->hasMany(Color::class);
    }
}

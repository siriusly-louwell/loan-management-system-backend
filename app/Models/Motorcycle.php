<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Color;
use App\Models\Image;

class Motorcycle extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'brand', 'color', 'description', 'price', 'quantity', 'file_path', 'interest', 'rebate', 'tenure'];

    public function colors()
    {
        return $this->hasMany(Color::class);
    }

    public function images()
    {
        return $this->hasMany(Image::class);
    }
}

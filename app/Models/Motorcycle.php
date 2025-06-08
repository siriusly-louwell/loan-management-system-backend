<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Color;
use App\Models\Image;
use App\Models\Transaction;

class Motorcycle extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'brand', 'color', 'description', 'price', 'quantity', 'file_path', 'interest', 'rebate', 'tenure', 'downpayment',
        'engine', 'compression', 'displacement', 'horsepower', 'torque', 'fuel', 'drive', 'transmission', 'cooling', 'front_suspension', 'rear_suspension',
        'frame', 'travel', 'swingarm', 'dry_weight', 'wet_weight', 'seat', 'wheelbase', 'fuel_tank', 'clearance', 'tires', 'wheel', 'brakes', 'abs', 'traction',
        'tft', 'lighting', 'ride_mode', 'quickshifter', 'cruise'
    ];

    public function colors()
    {
        return $this->hasMany(Color::class);
    }

    public function images()
    {
        return $this->hasMany(Image::class);
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }
}

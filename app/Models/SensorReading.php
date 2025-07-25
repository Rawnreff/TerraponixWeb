<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SensorReading extends Model
{
    use HasFactory;

    protected $fillable = [
        'device_id',
        'temperature',
        'humidity',
        'ph_value',
        'light_intensity',
        'water_level',
        'co2_level',
        'soil_moisture'
    ];

    public function device()
    {
        return $this->belongsTo(Device::class);
    }
}
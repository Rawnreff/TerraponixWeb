<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    use HasFactory;

    protected $fillable = [
        'device_id',
        'temp_threshold',
        'light_threshold',
        'water_level_threshold',
        'ph_min',
        'ph_max',
        'auto_mode'
    ];

    protected $casts = [
        'auto_mode' => 'boolean'
    ];

    public function device()
    {
        return $this->belongsTo(Device::class);
    }
}
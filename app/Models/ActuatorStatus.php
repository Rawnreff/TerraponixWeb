<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ActuatorStatus extends Model
{
    use HasFactory;

    protected $fillable = [
        'device_id',
        'curtain_position',
        'fan_status',
        'water_pump_status',
        'auto_mode',
        'last_updated'
    ];

    protected $casts = [
        'fan_status' => 'boolean',
        'water_pump_status' => 'boolean',
        'auto_mode' => 'boolean',
        'last_updated' => 'datetime'
    ];

    // misal tidak ingin Eloquent mengelola timestamps
    public $timestamps = false;

    public function device()
    {
        return $this->belongsTo(Device::class);
    }
}
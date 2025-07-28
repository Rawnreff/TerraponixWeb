<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Device extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'location',
        'ip_address',
        'status',
        'last_seen'
    ];

    protected $casts = [
        'last_seen' => 'datetime'
    ];

    public function sensorReadings()
    {
        return $this->hasMany(SensorReading::class);
    }

    public function actuatorStatus()
    {
        return $this->hasOne(ActuatorStatus::class);
    }

    public function settings()
    {
        return $this->hasOne(Setting::class);
    }

    public function actuatorLogs()
    {
        return $this->hasMany(ActuatorLog::class);
    }
}
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ActuatorLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'device_id',
        'actuator_type',
        'action',
        'value',
        'timestamp'
    ];

    protected $casts = [
        'timestamp' => 'datetime'
    ];

    public function device()
    {
        return $this->belongsTo(Device::class);
    }
}
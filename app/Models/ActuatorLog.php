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
        'old_value',
        'new_value',
        'triggered_by',
        'notes'
    ];

    protected $casts = [
        'old_value' => 'array',
        'new_value' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    public function device()
    {
        return $this->belongsTo(Device::class);
    }

    /**
     * Create a log entry for actuator control
     */
    public static function createLog($deviceId, $actuatorType, $oldValue, $newValue, $triggeredBy = 'manual', $notes = null)
    {
        return self::create([
            'device_id' => $deviceId,
            'actuator_type' => $actuatorType,
            'action' => $triggeredBy === 'auto' ? 'auto_control' : 'manual_control',
            'old_value' => is_array($oldValue) ? $oldValue : [$actuatorType => $oldValue],
            'new_value' => is_array($newValue) ? $newValue : [$actuatorType => $newValue],
            'triggered_by' => $triggeredBy,
            'notes' => $notes
        ]);
    }

    /**
     * Get recent logs for a device
     */
    public static function getRecentLogs($deviceId, $limit = 10)
    {
        return self::where('device_id', $deviceId)
            ->with('device')
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Get logs by actuator type
     */
    public static function getLogsByActuator($deviceId, $actuatorType, $limit = 10)
    {
        return self::where('device_id', $deviceId)
            ->where('actuator_type', $actuatorType)
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }
}
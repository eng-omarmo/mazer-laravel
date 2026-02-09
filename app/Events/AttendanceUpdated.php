<?php

namespace App\Events;

use App\Models\AttendanceLog;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class AttendanceUpdated
{
    use Dispatchable, SerializesModels;

    public AttendanceLog $log;
    public ?int $deviceStatus;

    public function __construct(AttendanceLog $log, ?int $deviceStatus = null)
    {
        $this->log = $log;
        $this->deviceStatus = $deviceStatus;
    }
}

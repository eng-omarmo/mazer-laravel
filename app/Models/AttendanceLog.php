<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AttendanceLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'date',
        'check_in',
        'check_out',
        'status',
        'source',
        'shift_type',
        'expected_check_out',
        'overtime_minutes',
        'early_arrival',
        'late_checkin',
        'missed_checkout',
    ];

    protected $casts = [
        'date' => 'date',
        'check_in' => 'datetime:H:i',
        'check_out' => 'datetime:H:i',
        'expected_check_out' => 'datetime:H:i',
        'overtime_minutes' => 'integer',
        'early_arrival' => 'boolean',
        'late_checkin' => 'boolean',
        'missed_checkout' => 'boolean',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
}

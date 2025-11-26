<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmployeeDocument extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'type',
        'path',
        'status',
        'uploaded_by',
        'verified_by',
        'verified_at',
        'rejection_reason',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
}

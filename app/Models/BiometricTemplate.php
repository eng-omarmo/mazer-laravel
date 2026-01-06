<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BiometricTemplate extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'device_sn',
        'algorithm',
        'dpi',
        'quality_score',
        'captured_at',
        'ciphertext',
        'iv',
        'tag',
        'created_by',
    ];

    protected $casts = [
        'captured_at' => 'datetime',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
}


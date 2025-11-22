<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'direction','type','amount','reference','batch_id','employee_id','posted_by','posted_at','status','meta'
    ];

    protected $casts = [
        'posted_at' => 'datetime',
        'meta' => 'array',
    ];

    public function batch()
    {
        return $this->belongsTo(PayrollBatch::class, 'batch_id');
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
}
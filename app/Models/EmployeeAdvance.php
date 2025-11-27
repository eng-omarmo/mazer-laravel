<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmployeeAdvance extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'date',
        'amount',
        'remaining_balance',
        'installment_amount',
        'next_due_date',
        'schedule_type',
        'reason',
        'status',
        'approved_by',
        'approved_at',
        'paid_by',
        'paid_at',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function transactions()
    {
        return $this->hasMany(AdvanceTransaction::class, 'advance_id');
    }

    public function isOverdue(): bool
    {
        return ($this->remaining_balance ?? $this->amount) > 0
            && $this->next_due_date
            && \Carbon\Carbon::parse($this->next_due_date)->isPast();
    }
}

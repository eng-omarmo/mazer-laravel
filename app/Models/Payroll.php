<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payroll extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'year',
        'month',
        'basic_salary',
        'allowances',
        'deductions',
        'net_pay',
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
}
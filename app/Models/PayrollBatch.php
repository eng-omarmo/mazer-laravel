<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PayrollBatch extends Model
{
    use HasFactory;

    protected $fillable = [
        'year', 'month', 'status', 'total_employees', 'total_amount',
        'posted_by', 'posted_at', 'submitted_by', 'submitted_at', 'approved_by', 'approved_at', 'rejected_by', 'rejected_at',
    ];

    public function payrolls()
    {
        return $this->hasMany(Payroll::class, 'batch_id');
    }
}

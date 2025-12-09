<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExpensePayment extends Model
{
    use HasFactory;

    protected $fillable = [
        'expense_id',
        'amount',
        'paid_at',
        'paid_by',
        'note',
        'is_approved',
    ];

    protected $casts = [
        'paid_at' => 'datetime',
    ];

    public function expense()
    {
        return $this->belongsTo(Expense::class);
    }
}

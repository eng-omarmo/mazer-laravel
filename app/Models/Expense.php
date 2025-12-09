<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Expense extends Model
{
    use HasFactory;

    protected $fillable = [
        'supplier_id',
        'organization_id',
        'type',
        'amount',
        'document_path',
        'status',
    ];

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    public function payments()
    {
        return $this->hasMany(ExpensePayment::class);
    }

    public function totalPaid(): float
    {
        return (float) $this->payments()->sum('amount');
    }

    public function remaining(): float
    {
        return max(0.0, (float) $this->amount - $this->totalPaid());
    }

    public function paymentStatus(): string
    {
        $rem = $this->remaining();
        if ($rem <= 0) {
            return 'paid';
        }
        if ($rem < (float) $this->amount) {
            return 'partial';
        }
        return 'pending';
    }

    public function approvalStatus() : string
    {
        $latestPayment = $this->payments()->latest()->first();
        if ($latestPayment) {
            return $latestPayment->approval_status;
        }
        return 'pending';
    }
}

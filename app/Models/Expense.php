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
        'payment_status',
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
        return (float) $this->payments()->where('status', 'approved')->sum('amount');
    }

    public function remaining(): float
    {
        return max(0.0, (float) $this->amount - $this->totalPaid());
    }

    public function updatePaymentStatus()
    {
        $paid = $this->totalPaid();
        if ($paid >= $this->amount - 0.0001) {
            $this->update(['payment_status' => 'paid']);
        } elseif ($paid > 0) {
            $this->update(['payment_status' => 'partial']);
        } else {
            $this->update(['payment_status' => 'pending']);
        }
    }

    public function approvalStatus() : string
    {
        $latestPayment = $this->payments()->latest()->first();
        if ($latestPayment) {
            return $latestPayment->status;
        }
        return 'pending';
    }


}

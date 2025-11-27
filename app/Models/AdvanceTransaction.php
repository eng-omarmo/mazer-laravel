<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AdvanceTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'advance_id',
        'type',
        'amount',
        'reference_type',
        'reference_id',
        'created_by',
    ];

    public function advance()
    {
        return $this->belongsTo(EmployeeAdvance::class, 'advance_id');
    }
}

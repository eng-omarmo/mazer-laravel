<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{
    use HasFactory;

    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'department_id',
        'salary',
        'bonus',
        'reference_full_name',
        'reference_phone',
        'identity_doc_number',
        'fingerprint_id',
        'position',
        'hire_date',
        'status',
    ];

    public function documents()
    {
        return $this->hasMany(EmployeeDocument::class);
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }
}
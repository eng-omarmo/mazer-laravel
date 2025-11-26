<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Department extends Model
{
    use HasFactory;

    protected $fillable = ['code', 'name', 'head_employee_id'];

    public function head()
    {
        return $this->belongsTo(Employee::class, 'head_employee_id');
    }

    public function employees()
    {
        return $this->hasMany(Employee::class);
    }
}

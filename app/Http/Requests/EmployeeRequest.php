<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class EmployeeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $emp = $this->route('employee');
        $ignoreId = is_object($emp) ? $emp->employee_id : null;
        return [
            'first_name' => ['required','string','max:255'],
            'last_name' => ['required','string','max:255'],
            'email' => [
                'required','email','max:255',
                Rule::unique('employees','email')->ignore($ignoreId,'employee_id')
            ],
            'phone' => ['nullable','string','max:255'],
            'address' => ['nullable','string'],
            'dob' => ['nullable','date'],
            'gender' => ['nullable', Rule::in(['male','female','other'])],
            'department_id' => ['required','integer', 'exists:departments,department_id'],
            'designation' => ['nullable','string','max:255'],
            'join_date' => ['nullable','date'],
            'employment_type' => ['required', Rule::in(['full-time','part-time','contract'])],
            'status' => ['required', Rule::in(['active','resigned','terminated'])],
        ];
    }
}
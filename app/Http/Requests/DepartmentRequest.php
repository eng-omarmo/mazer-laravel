<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class DepartmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $dept = $this->route('department');
        $ignoreId = is_object($dept) ? $dept->department_id : null;
        return [
            'name' => [
                'required','string','max:255',
                Rule::unique('departments','name')->ignore($ignoreId,'department_id')
            ],
            'description' => ['nullable','string']
        ];
    }
}
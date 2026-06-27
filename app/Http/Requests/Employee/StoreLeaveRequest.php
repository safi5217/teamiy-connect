<?php

namespace App\Http\Requests\Employee;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Log;
use Illuminate\Contracts\Validation\Validator;

class StoreLeaveRequest extends FormRequest
{
    public function authorize(): bool
    {
        Log::info('StoreLeaveRequest authorize hit', [
            'user_id' => $this->user()?->id,
            'company_id' => $this->user()?->company_id,
            'branch_id' => $this->user()?->branch_id,
            'payload' => $this->all(),
        ]);

        return $this->user() !== null;
    }

    public function rules(): array
    {
        Log::info('StoreLeaveRequest rules hit', [
            'payload' => $this->all(),
        ]);

        return [
            'title' => ['nullable', 'string', 'max:191'],
            'leave_type_id' => [
                'required',
                Rule::exists('leave_types', 'id'),
            ],
            'leave_from' => ['required', 'date'],
            'leave_to' => ['required', 'date', 'after_or_equal:leave_from'],
            'reasons' => ['required', 'string', 'max:5000'],
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        Log::error('StoreLeaveRequest validation failed', [
            'errors' => $validator->errors()->toArray(),
            'payload' => $this->all(),
        ]);

        parent::failedValidation($validator);
    }
}

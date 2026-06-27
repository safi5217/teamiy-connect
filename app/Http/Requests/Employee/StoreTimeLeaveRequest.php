<?php

namespace App\Http\Requests\Employee;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Log;
use Illuminate\Contracts\Validation\Validator;

class StoreTimeLeaveRequest extends FormRequest
{
    public function authorize(): bool
    {
        Log::info('StoreTimeLeaveRequest authorize hit', [
            'user_id' => $this->user()?->id,
            'company_id' => $this->user()?->company_id,
            'branch_id' => $this->user()?->branch_id,
            'payload' => $this->all(),
        ]);

        return $this->user() !== null;
    }

    public function rules(): array
    {
        Log::info('StoreTimeLeaveRequest rules hit', [
            'payload' => $this->all(),
        ]);

        return [
            'issue_date' => ['required', 'date'],
            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => ['required', 'date_format:H:i', 'after:start_time'],
            'reasons' => ['required', 'string', 'max:5000'],
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        Log::error('StoreTimeLeaveRequest validation failed', [
            'errors' => $validator->errors()->toArray(),
            'payload' => $this->all(),
        ]);

        parent::failedValidation($validator);
    }
}
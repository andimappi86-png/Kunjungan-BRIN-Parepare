<?php

namespace App\Http\Requests;

use App\Models\Visit;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateVisitRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'status' => ['required', Rule::in(Visit::statuses())],
            'scheduled_date' => ['nullable', 'date'],
            'visit_time' => ['nullable', 'date_format:H:i'],
        ];
    }
}

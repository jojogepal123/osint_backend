<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCaseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'status' => 'sometimes|in:open,in_progress,pending,resolved,closed',
            'priority' => 'sometimes|in:low,medium,high,critical',
            'category' => 'nullable|string|max:100',
            'assigned_to' => 'nullable|array',
            'assigned_to.*' => 'integer|exists:users,id',
        ];
    }
}

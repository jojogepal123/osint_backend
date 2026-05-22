<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCaseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'status' => 'nullable|in:open,in_progress,pending,resolved,closed',
            'priority' => 'nullable|in:low,medium,high,critical',
            'category' => 'nullable|string|max:100',
            'assigned_to' => 'nullable|array',
            'assigned_to.*' => 'integer|exists:users,id',
        ];
    }
}

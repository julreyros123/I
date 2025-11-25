<?php

namespace App\Http\Requests\Meter;

use Illuminate\Foundation\Http\FormRequest;

class StoreMeterRequest extends FormRequest
{
    public function authorize(): bool { return auth()->check(); }

    public function rules(): array
    {
        return [
            'serial' => 'required|string|max:255|unique:meters,serial',
            'type' => 'nullable|string|max:255',
            'size' => 'nullable|string|max:255',
            'manufacturer' => 'nullable|string|max:255',
            'seal_no' => 'nullable|string|max:255',
            'status' => 'required|in:inventory,installed,active,maintenance,inactive,retired',
            'install_date' => 'nullable|date',
            'location_address' => 'nullable|string|max:1000',
            'barangay' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
        ];
    }
}

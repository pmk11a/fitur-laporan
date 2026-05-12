<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * {Module} Request Validation
 *
 * Migrated from: Delphi Frm{Xxx} validation logic
 */
class {Module}Request extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Return true for now, or add authorization logic
        return true;
    }

    /**
     * Get validation rules that apply to the request.
     *
     * Migrated from: Delphi validation (e.g., FieldExit events)
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'field1' => 'required|string|max:255',
            'field2' => 'required|integer|min:1|max:9999',
            'field3' => 'nullable|date',
            'field4' => 'nullable|numeric|min:0',
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array
     */
    public function messages(): array
    {
        return [
            'field1.required' => 'Field 1 wajib diisi',
            'field1.max' => 'Field 1 maksimal 255 karakter',
            'field2.required' => 'Field 2 wajib diisi',
            'field2.min' => 'Field 2 minimal 1',
            'field2.max' => 'Field 2 maksimal 9999',
        ];
    }
}

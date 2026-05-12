<?php

namespace App\Http\Requests\V1;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

/**
 * Update{{ModelName}} Request
 *
 * Generated from: {{DelphiForm}}
 * Validation rules for updating existing records
 *
 * @package App\Http\Requests\V1
 */
class Update{{ModelName}}Request extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        ${{PrimaryKeySnake}} = $this->route('{{ModelNameLower}}')->{{PrimaryKey}};

        return [
            {{UpdateValidationRules}}
        ];
    }

    /**
     * Get custom error messages for validator
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            {{UpdateErrorMessages}}
        ];
    }
}

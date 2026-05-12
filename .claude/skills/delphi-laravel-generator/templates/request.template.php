<?php

namespace App\Http\Requests\V1;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

/**
 * Store{{ModelName}} Request
 *
 * Generated from: {{DelphiForm}}
 * Validation rules extracted from Cek* functions
 *
 * @package App\Http\Requests\V1
 */
class Store{{ModelName}}Request extends FormRequest
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
     * From Delphi: CekKosong, CekAngka, CekTanggal, CekPeriode, etc.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            {{ValidationRules}}
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
            {{ErrorMessages}}
        ];
    }

    /**
     * Get custom attributes for validator
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            {{AttributeNames}}
        ];
    }

    /**
     * Handle a failed validation attempt
     *
     * @param Validator $validator
     * @throws HttpResponseException
     */
    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(
            response()->json([
                'message' => 'Validasi gagal',
                'errors' => $validator->errors()
            ], 422)
        );
    }

    // =========================================================================
    // CUSTOM VALIDATION (From Delphi business logic)
    // =========================================================================

    {{CustomValidationMethods}}
}

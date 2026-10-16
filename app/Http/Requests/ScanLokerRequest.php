<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ScanLokerRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
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
        return [
            'barcode' => ['required', 'string', 'max:100'],
            'gender' => ['nullable', 'string', 'in:L,P,l,p'],
            'idempotency_key' => ['nullable', 'string', 'max:64'],
        ];
    }

    /**
     * Custom messages for validation errors.
     */
    public function messages(): array
    {
        return [
            'barcode.required' => 'Barcode ID Card wajib dipindai.',
            'gender.in' => 'Pilihan kelompok gender tidak valid (hanya L atau P).',
        ];
    }
}

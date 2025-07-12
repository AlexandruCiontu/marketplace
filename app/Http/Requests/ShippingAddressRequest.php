<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ShippingAddressRequest extends FormRequest
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
            'country_code' => ['required', 'string', 'exists:countries,code'],
            'full_name' => ['required', 'string'],
            'type' => ['required', 'string'],
            'phone' => ['required', 'string'],
            'city' => ['required', 'string'],
            'zipcode' => ['required', 'string'],
            'address1' => ['required', 'string'],
            'address2' => ['nullable', 'string'],
            'state' => ['nullable', 'required_if:country_code,USA', 'string'],
            'default' => ['required', 'boolean'],
            'delivery_instructions' => ['nullable', 'string'],
        ];
    }

    public function messages()
    {
        return [
            'state.required_if' => 'The state field is required',
        ];
    }

    /**
     * Handle a passed validation attempt.
     */
    protected function prepareForValidation()
    {
        $this->merge([
            'type' => 'shipping',
        ]);
    }
}

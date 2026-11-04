<?php

namespace App\Http\Requests;

use Illuminate\Validation\Rule;

class StoreCustomerRequest extends ApiRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'min:2', 'max:255'],
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('customers', 'email'),
            ],
            'phone' => [
                'nullable',
                'string',
                'regex:/^[0-9]{10,11}$/',
            ],
        ];
    }
}

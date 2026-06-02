<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CustomerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $id = $this->route('customer');

        return [
            'first_name' => ['required', 'string', 'max:50'],
            'last_name'  => ['required', 'string', 'max:50'],
            'email'      => ['nullable', 'email', 'unique:customers,email,' . $id],
            'phone'      => ['nullable', 'string', 'max:20'],
            'address'    => ['nullable', 'string'],
        ];
    }
}

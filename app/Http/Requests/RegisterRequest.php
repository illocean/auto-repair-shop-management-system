<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RegisterRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'first_name' => ['required', 'string', 'max:50'],
            'last_name'  => ['required', 'string', 'max:50'],
            'email'      => ['required', 'email', 'max:100'],
            'phone'      => ['nullable', 'string', 'max:20'],
            'address'    => ['nullable', 'string', 'max:255'],
            'password'   => ['required', 'string', 'min:8', 'confirmed'],
        ];
    }
}

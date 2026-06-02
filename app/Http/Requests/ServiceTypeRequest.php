<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ServiceTypeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $id = $this->route('service_type');

        return [
            'name'          => ['required', 'string', 'max:100', 'unique:service_types,name,' . $id],
            'description'   => ['nullable', 'string'],
            'book_hours'    => ['required', 'numeric', 'min:0.01', 'max:999.99'],
            'rate_per_hour' => ['required', 'numeric', 'min:0.01', 'max:99999.99'],
        ];
    }
}

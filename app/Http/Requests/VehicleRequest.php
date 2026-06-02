<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class VehicleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $id = $this->route('vehicle');

        return [
            'customer_id'   => ['exists:customers,id'],
            'make'          => ['required', 'string', 'max:50'],
            'model'         => ['required', 'string', 'max:50'],
            'year'          => ['nullable', 'integer', 'min:1901', 'max:' . (date('Y') + 1)],
            'license_plate' => ['nullable', 'string', 'max:20'],
            'vin'           => ['nullable', 'string', 'max:17'],
        ];
    }
}

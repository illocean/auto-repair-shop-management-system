<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\DB;

class StoreUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        $role = session('role');
        return in_array($role, ['admin', 'manager']);
    }

    public function rules(): array
    {
        return [
            'first_name' => ['required', 'string', 'max:50'],
            'last_name'  => ['required', 'string', 'max:50'],
            'email'      => ['required', 'email', 'unique:users,email'],
            'password'   => ['required', 'string', 'min:6'],
            'role_id'    => ['required', 'exists:roles,id'],
            'is_active'  => ['nullable', 'boolean'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $roleId = $this->input('role_id');
        $userRole = session('role');

        if ($userRole !== 'admin') {
            $targetRoleName = DB::table('roles')->where('id', $roleId)->value('name');
            $targetLevel = $this->getRoleLevel($targetRoleName ?? '');
            $sessionLevel = $this->getRoleLevel($userRole);

            if ($targetLevel >= $sessionLevel) {
                abort(403, 'You can only assign roles below your own level.');
            }
        }
    }

    private function getRoleLevel(string $roleName): int
    {
        return match($roleName) {
            'admin'    => 4,
            'manager'  => 3,
            'staff'    => 2,
            'customer' => 1,
            default     => 0,
        };
    }
}

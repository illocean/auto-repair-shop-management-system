<?php

namespace App\Models;

use App\Models\Traits\Auditable;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, Auditable;

    protected $fillable = [
        'first_name', 'last_name', 'email', 'password', 'is_active', 'last_login', 'user_type',
    ];

    protected $hidden = [
        'password', 'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
            'user_type' => 'string',
        ];
    }

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'role_user');
    }

    public function customer(): HasOne
    {
        return $this->hasOne(Customer::class);
    }

    public function isCustomer(): bool
    {
        return $this->user_type === 'customer';
    }
}

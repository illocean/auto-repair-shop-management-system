<?php

namespace App\Models;

use App\Models\Traits\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ServiceType extends Model
{
    use Auditable;

    protected $fillable = [
        'name', 'description', 'book_hours', 'rate_per_hour',
    ];

    public function repairOrderServices(): HasMany
    {
        return $this->hasMany(RepairOrderService::class);
    }

    public function getCalculatedRateAttribute(): float
    {
        return $this->book_hours * $this->rate_per_hour;
    }
}

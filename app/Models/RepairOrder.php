<?php

namespace App\Models;

use App\Models\Traits\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RepairOrder extends Model
{
    use Auditable;

    protected $fillable = [
        'customer_id', 'vehicle_id', 'service_advisor_name', 'order_date',
        'status', 'notes', 'created_by', 'updated_by',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function services(): HasMany
    {
        return $this->hasMany(RepairOrderService::class);
    }

    public function getTotalAttribute(): float
    {
        return $this->services->sum('line_total');
    }

    public function canTransitionTo(string $newStatus): bool
    {
        $allowed = [
            'open'        => ['in_progress', 'cancelled'],
            'in_progress' => ['completed', 'cancelled'],
            'completed'   => [],
            'cancelled'   => [],
        ];

        return in_array($newStatus, $allowed[$this->status] ?? []);
    }
}

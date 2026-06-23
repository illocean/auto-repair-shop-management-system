<?php

namespace App\Models;

use App\Models\Traits\Auditable;
use Illuminate\Database\Eloquent\Model;

class Supply extends Model
{
    use Auditable;

    protected $fillable = [
        'name', 'description', 'quantity', 'unit', 'unit_price', 'low_stock_threshold',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'integer',
            'unit_price' => 'decimal:2',
            'low_stock_threshold' => 'integer',
        ];
    }

    public function isLowStock(): bool
    {
        return $this->quantity <= $this->low_stock_threshold;
    }
}

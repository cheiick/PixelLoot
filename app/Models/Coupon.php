<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Coupon extends Model
{
    protected $fillable = [
        'code',
        'type',
        'value',
        'min_purchase',
        'usage_limit',
        'used_count',
        'valid_from',
        'valid_until',
        'is_active'
    ];

    protected $casts = [
        'valid_from' => 'date',
        'valid_until' => 'date',
        'is_active' => 'boolean'
    ];

    public function isAvailableFor(float $subtotal): bool
    {
        return $this->is_active
            && (!$this->valid_from || $this->valid_from->lte(today()))
            && (!$this->valid_until || $this->valid_until->gte(today()))
            && (!$this->usage_limit || $this->used_count < $this->usage_limit)
            && $subtotal >= $this->min_purchase;
    }

    public function discountFor(float $subtotal): float
    {
        if (!$this->isAvailableFor($subtotal)) {
            return 0;
        }

        $discount = $this->type === 'percentage'
            ? $subtotal * ((float) $this->value / 100)
            : (float) $this->value;

        return min($discount, $subtotal);
    }
}

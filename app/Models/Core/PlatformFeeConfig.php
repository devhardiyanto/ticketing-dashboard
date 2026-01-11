<?php

namespace App\Models\Core;

use Illuminate\Database\Eloquent\Model;

class PlatformFeeConfig extends Model
{
    protected $connection = 'core_pgsql';
    protected $table = 'platform_fee_config';
    protected $primaryKey = 'id';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'percentage_fee',
        'fixed_fee',
        'is_active',
    ];

    protected $casts = [
        'percentage_fee' => 'decimal:2',
        'fixed_fee' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    /**
     * Calculate platform fee for a given subtotal
     * Formula: (subtotal × percentage / 100) + fixedFee
     */
    public function calculateFee(float $subtotal): float
    {
        if (!$this->is_active) {
            return 0;
        }

        $percentageAmount = ($subtotal * $this->percentage_fee) / 100;
        return round($percentageAmount + $this->fixed_fee);
    }
}

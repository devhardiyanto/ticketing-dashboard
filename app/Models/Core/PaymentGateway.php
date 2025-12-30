<?php

namespace App\Models\Core;

use Illuminate\Database\Eloquent\Model;

/**
 * Payment Gateway Model (Core Schema)
 *
 * This model represents payment gateways from the core.payment_gateways table.
 * It's shared between Core (Node.js) and Dashboard (Laravel) via cross-schema access.
 *
 * Dashboard can manage gateway configurations via this model.
 *
 * @property int $id
 * @property string $code
 * @property string $name
 * @property string|null $description
 * @property bool $is_active
 * @property bool $is_sandbox
 * @property string $encrypted_config
 * @property array|null $settings
 * @property array|null $supported_methods
 * @property array|null $fee_config
 * @property int $priority
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 */
class PaymentGateway extends Model
{
    /**
     * The connection name for the model.
     *
     * @var string
     */
    protected $connection = 'core_pgsql';

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'payment_gateways';

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'id';

    /**
     * The "type" of the primary key ID.
     *
     * @var string
     */
    protected $keyType = 'string';

    /**
     * Indicates if the IDs are auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'code',
        'name',
        'description',
        'is_active',
        'is_sandbox',
        'encrypted_config',
        'settings',
        'supported_methods',
        'fee_config',
        'priority',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_active' => 'boolean',
        'is_sandbox' => 'boolean',
        'settings' => 'array',
        'supported_methods' => 'array',
        'fee_config' => 'array',
        'priority' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'encrypted_config', // Never expose encrypted config directly
    ];

    /**
     * Scope to get only active gateways
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to get only production gateways
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeProduction($query)
    {
        return $query->where('is_sandbox', false);
    }

    /**
     * Scope to get only sandbox gateways
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeSandbox($query)
    {
        return $query->where('is_sandbox', true);
    }

    /**
     * Scope to order by priority (highest first)
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopePrioritized($query)
    {
        return $query->orderBy('priority', 'desc');
    }

    /**
     * Check if gateway is active
     *
     * @return bool
     */
    public function isActive(): bool
    {
        return $this->is_active === true;
    }

    /**
     * Check if gateway is in sandbox mode
     *
     * @return bool
     */
    public function isSandbox(): bool
    {
        return $this->is_sandbox === true;
    }

    /**
     * Check if gateway config is set (not placeholder)
     *
     * @return bool
     */
    public function hasValidConfig(): bool
    {
        return $this->encrypted_config !== 'PLACEHOLDER_CONFIG';
    }

    /**
     * Get display status
     *
     * @return string
     */
    public function getStatusAttribute(): string
    {
        if (!$this->is_active) {
            return 'inactive';
        }

        if (!$this->hasValidConfig()) {
            return 'not_configured';
        }

        return $this->is_sandbox ? 'sandbox' : 'production';
    }

    /**
     * Get status badge color for UI
     *
     * @return string
     */
    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'production' => 'green',
            'sandbox' => 'yellow',
            'not_configured' => 'orange',
            'inactive' => 'gray',
            default => 'gray',
        };
    }

    /**
     * Get gateway icon/logo URL
     *
     * @return string
     */
    public function getIconUrlAttribute(): string
    {
        $icons = [
            'midtrans' => '/images/gateways/midtrans.png',
            'xendit' => '/images/gateways/xendit.png',
            'nicepay' => '/images/gateways/nicepay.png',
        ];

        return $icons[$this->code] ?? '/images/gateways/default.png';
    }

    /**
     * Get human-readable priority label
     *
     * @return string
     */
    public function getPriorityLabelAttribute(): string
    {
        return match (true) {
            $this->priority >= 3 => 'High',
            $this->priority === 2 => 'Medium',
            $this->priority === 1 => 'Low',
            default => 'None',
        };
    }

    /**
     * Check if gateway supports a specific payment method
     *
     * @param string $method Payment method code
     * @return bool
     */
    public function supportsMethod(string $method): bool
    {
        if (!is_array($this->supported_methods)) {
            return false;
        }

        return in_array($method, $this->supported_methods);
    }

    /**
     * Get formatted list of supported methods
     *
     * @return string
     */
    public function getSupportedMethodsListAttribute(): string
    {
        if (!is_array($this->supported_methods)) {
            return 'None';
        }

        return implode(', ', array_map('ucfirst', $this->supported_methods));
    }
}

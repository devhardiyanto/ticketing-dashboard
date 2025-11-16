<?php

namespace App\Models\Core;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Organization Model (Core Schema)
 *
 * This model represents organizations from the core.organizations table.
 * It's shared between Core (Node.js) and Dashboard (Laravel) via cross-schema FK.
 *
 * Dashboard users reference this table via dashboard_users.organization_id (UUID).
 */
class Organization extends Model
{
    /**
     * The connection name for the model.
     *
     * @var string
     */
    protected $connection = 'pgsql';

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'core.organizations';

    /**
     * Indicates if the IDs are auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = false;

    /**
     * The data type of the auto-incrementing ID.
     *
     * @var string
     */
    protected $keyType = 'string'; // UUID

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'id' => 'string',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'business_type',
        'email',
        'phone_number',
        'address',
        'tax_id',
        'status',
    ];

    /**
     * Get dashboard users for this organization (cross-schema relationship)
     *
     * @return HasMany
     */
    public function dashboardUsers(): HasMany
    {
        return $this->hasMany(\App\Models\Dashboard\User::class, 'organization_id');
    }

    /**
     * Get activity logs for this organization (cross-schema relationship)
     *
     * @return HasMany
     */
    public function activityLogs(): HasMany
    {
        return $this->hasMany(\App\Models\Dashboard\ActivityLog::class, 'organization_id');
    }

    /**
     * Check if organization is active
     *
     * @return bool
     */
    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    /**
     * Check if organization is inactive
     *
     * @return bool
     */
    public function isInactive(): bool
    {
        return $this->status === 'inactive';
    }

    /**
     * Check if organization is suspended
     *
     * @return bool
     */
    public function isSuspended(): bool
    {
        return $this->status === 'suspended';
    }

    /**
     * Scope to get only active organizations
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope to get organizations by business type
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $type
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeByBusinessType($query, string $type)
    {
        return $query->where('business_type', $type);
    }
}

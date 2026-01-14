<?php

namespace App\Models\Dashboard;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;

use App\Models\Core\Organization;

class User extends Authenticatable
{
  use HasFactory, Notifiable, TwoFactorAuthenticatable, SoftDeletes;
  use \Spatie\Permission\Traits\HasRoles;
  use \Spatie\Activitylog\Traits\LogsActivity;

  public function getActivitylogOptions(): \Spatie\Activitylog\LogOptions
  {
      return \Spatie\Activitylog\LogOptions::defaults()->logOnly(['*']);
  }


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
  protected $table = 'users';

  /**
   * The attributes that are mass assignable.
   *
   * @var list<string>
   */
  protected $fillable = [
    'name',
    'email',
    'password',
    'phone_number',
    'organization_id',
    'status',
    'last_login_at',
  ];

  /**
   * The attributes that should be hidden for serialization.
   *
   * @var list<string>
   */
  protected $hidden = [
    'password',
    'two_factor_secret',
    'two_factor_recovery_codes',
    'remember_token',
  ];

  /**
   * Get the attributes that should be cast.
   *
   * @return array<string, string>
   */
  protected function casts(): array
  {
    return [
      'organization_id' => 'string', // UUID cast
      'email_verified_at' => 'datetime',
      'password' => 'hashed',
      'two_factor_confirmed_at' => 'datetime',
      'last_login_at' => 'datetime',
    ];
  }

  /**
   * Get the organization that the user belongs to (cross-schema relationship to core.organizations)
   */
  public function organization(): BelongsTo
  {
    return $this->belongsTo(Organization::class, 'organization_id');
  }

  /**
   * Get the activity logs for the user.
   */
  public function activityLogs(): HasMany
  {
    return $this->hasMany(ActivityLog::class, 'user_id');
  }

  /**
   * Check if user is active.
   */
  public function isActive(): bool
  {
    return $this->status === 'active';
  }

  /**
   * Scope a query to only include active users.
   */
  public function scopeActive($query)
  {
    return $query->where('status', 'active');
  }

  /**
   * Scope a query to only include users from a specific organization.
   *
   * @param string $organizationId UUID of the organization
   */
  public function scopeForOrganization($query, string $organizationId)
  {
    return $query->where('organization_id', $organizationId);
  }
}

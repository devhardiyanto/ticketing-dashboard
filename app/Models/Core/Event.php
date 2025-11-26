<?php

namespace App\Models\Core;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Event extends Model
{
  protected $connection = 'core_pgsql';
  protected $table = 'events';
  protected $primaryKey = 'id';
  protected $keyType = 'string';
  public $incrementing = false;

  protected $fillable = [
    'organization_id',
    'name',
    'description',
    'start_date',
    'end_date',
    'location',
    'status',
    'is_parent',
    'parent_event_id',
  ];

  protected $casts = [
    'start_date' => 'datetime',
    'end_date' => 'datetime',
    'is_parent' => 'boolean', // Note: Schema uses varchar 'true'/'false', might need accessor/mutator if not handled by DB driver
  ];

  public function organization(): BelongsTo
  {
    return $this->belongsTo(Organization::class, 'organization_id');
  }

  public function ticketTypes(): HasMany
  {
    return $this->hasMany(TicketType::class, 'event_id');
  }
}

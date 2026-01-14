<?php

namespace App\Models\Core;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Event extends Model
{
    use LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logOnly(['*']);
    }
	protected $connection = 'core_pgsql';
	protected $table = 'events';
	protected $primaryKey = 'id';
	protected $keyType = 'string';
	public $incrementing = false;

	protected $fillable = [
		'organization_id',
		'name',
		'slug',
		'description',
		'start_date',
		'end_date',
		'timezone',
		'location',
		'address',
		'venue_name',
		'venue_city',
		'image_url',
		'banner_image_url',
		'venue_map_url',
		'terms',
		'status',
		'currency',
		'is_parent',
		'parent_event_id',
	];

	protected $casts = [
		'start_date' => 'datetime',
		'end_date' => 'datetime',
		'is_parent' => 'boolean', // Note: Schema uses varchar 'true'/'false', might need accessor/mutator if not handled by DB driver
		'currency' => 'array',
	];

	public function childEvents(): HasMany
	{
		return $this->hasMany(Event::class, 'parent_event_id');
	}

	public function organization(): BelongsTo
	{
		return $this->belongsTo(Organization::class, 'organization_id')->select('id', 'name');
	}

	public function ticketTypes(): HasMany
	{
		return $this->hasMany(TicketType::class, 'event_id');
	}
}

<?php

namespace App\Models\Core;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class TicketType extends Model
{
    use LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logOnly(['*']);
    }
  protected $connection = 'core_pgsql';
  protected $table = 'ticket_types';
  protected $primaryKey = 'id';
  protected $keyType = 'string';
  public $incrementing = false;

  protected $fillable = [
    'event_id',
    'name',
    'category',
    'description',
    'price',
    'quantity',
    'quantity_available',
    'max_per_order',
    'start_sale_date',
    'end_sale_date',
    'status',
    'is_hidden',
    'sort_order',
  ];

  protected $casts = [
    'price' => 'decimal:2',
    'start_sale_date' => 'datetime',
    'end_sale_date' => 'datetime',
    'is_hidden' => 'boolean',
    'sort_order' => 'integer',
  ];

  public function event(): BelongsTo
  {
    return $this->belongsTo(Event::class, 'event_id');
  }
}


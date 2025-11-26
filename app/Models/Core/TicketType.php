<?php

namespace App\Models\Core;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TicketType extends Model
{
  protected $connection = 'core_pgsql';
  protected $table = 'ticket_types';
  protected $primaryKey = 'id';
  protected $keyType = 'string';
  public $incrementing = false;

  protected $fillable = [
    'event_id',
    'name',
    'description',
    'price',
    'quantity',
    'sale_start_date',
    'sale_end_date',
  ];

  protected $casts = [
    'price' => 'decimal:2',
    'sale_start_date' => 'datetime',
    'sale_end_date' => 'datetime',
  ];

  public function event(): BelongsTo
  {
    return $this->belongsTo(Event::class, 'event_id');
  }
}

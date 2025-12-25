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
    'quantity_available',
    'start_sale_date',
    'end_sale_date',
  ];

  protected $casts = [
    'price' => 'decimal:2',
    'start_sale_date' => 'datetime',
    'end_sale_date' => 'datetime',
  ];

  public function event(): BelongsTo
  {
    return $this->belongsTo(Event::class, 'event_id');
  }
}

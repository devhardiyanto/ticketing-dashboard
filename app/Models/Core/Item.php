<?php

namespace App\Models\Core;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Item extends Model
{
    use LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logOnly(['*']);
    }

    protected $connection = 'core_pgsql';

    protected $table = 'items';

    protected $primaryKey = 'id';

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'event_id',
        'title',
        'category',
        'description',
        'price',
        'quantity',
        'quantity_available',
        'max_per_order',
        'start_sale_date',
        'end_sale_date',
        'status',
        'gimmick_status',
        'is_hidden',
        'sort_order',
        'is_invitation',
        'is_form_field',
        'item_type',
        'parent_item_id',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'start_sale_date' => 'datetime',
        'end_sale_date' => 'datetime',
        'is_hidden' => 'boolean',
        'is_invitation' => 'boolean',
        'is_form_field' => 'boolean',
        'sort_order' => 'integer',
        'gimmick_status' => 'integer',
    ];

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class, 'event_id');
    }
}

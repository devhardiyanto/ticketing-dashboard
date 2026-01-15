<?php

namespace App\Models\Core;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Order extends Model
{
    use LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logOnly(['*']);
    }

    protected $connection = 'core_pgsql';

    protected $table = 'orders';

    protected $primaryKey = 'id';

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'order_code',
        'user_id',
        'encrypted_guest_name',
        'guest_name_hash',
        'encrypted_guest_email',
        'guest_email_hash',
        'encrypted_guest_phone',
        'guest_phone_hash',
        'guest_name',
        'guest_email',
        'guest_phone_number',
        'total_amount',
        'platform_fee_amount',
        'status',
        'payment_method',
        'expires_at',
        'payment_deadline',
    ];

    protected $casts = [
        'total_amount' => 'decimal:2',
        'platform_fee_amount' => 'decimal:2',
        'expires_at' => 'datetime',
        'payment_deadline' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class, 'order_id');
    }

    /**
     * Get the event for this order (via first order item's ticket type)
     */
    public function getEventAttribute()
    {
        $item = $this->items()->with('ticketType.event')->first();

        return $item?->ticketType?->event;
    }
}

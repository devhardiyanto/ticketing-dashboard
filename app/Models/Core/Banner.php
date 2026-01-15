<?php

namespace App\Models\Core;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Banner extends Model
{
    protected $connection = 'core_pgsql';

    protected $table = 'banners';

    protected $primaryKey = 'id';

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'title',
        'image',
        'event_id',
        'status',
        'sequence',
    ];

    protected $casts = [
        'sequence' => 'integer',
        'status' => 'string',
    ];

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class, 'event_id')->select('id', 'name', 'slug');
    }
}

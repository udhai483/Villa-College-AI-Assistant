<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChatMetric extends Model
{
    protected $fillable = [
        'user_id',
        'conversation_id',
        'query',
        'response_time_ms',
        'search_method',
        'result_count',
        'error',
        'had_fallback',
    ];

    protected $casts = [
        'had_fallback' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function conversation(): BelongsTo
    {
        return $this->belongsTo(Conversation::class);
    }
}

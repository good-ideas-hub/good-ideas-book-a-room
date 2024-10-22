<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Event extends Model
{
    protected $fillable = [
        'room_id',
        'name',
        'book_by',
        'from',
        'to',
        'expected_participants',
    ];

    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }

    public function bookBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id');
    }
}

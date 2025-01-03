<?php

namespace App\Models;

use App\Enums\WantToKnowType;
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
        'type',
    ];

    protected $casts = [
        'type' => WantToKnowType::class,
    ];

    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }

    public function bookBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'book_by');
    }

    public static function isConflict(array $newEvent, ?Event $record = null): bool
    {
        $query = Event::where('room_id', $newEvent['room_id'])
            ->where('from', '<', $newEvent['to'])
            ->where('to', '>', $newEvent['from']);

        if ($record) {
            $query->where('id', '!=', $record->id);
        }

        return $query->exists();
    }
}

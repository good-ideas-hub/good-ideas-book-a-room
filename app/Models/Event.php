<?php

namespace App\Models;

use App\Enums\WantToKnowType;
use App\Services\EventService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Event extends Model
{
    protected $fillable = [
        'room_id',
        'name',
        'book_by',
        'speaker',
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
        if (array_key_exists('room_id', $newEvent)) {
            $query = Event::where('room_id', $newEvent['room_id'])
                ->whereNull('type')
                ->whereDate('from', '=', $newEvent['from'])
                ->where('from', '<', $newEvent['from'])
                ->where('to', '>', $newEvent['to']);

            if ($record) {
                $query->where('id', '!=', $record->id);
            }

            return $query->exists();
        }

        return false;
    }

    public static function isWantToKnow(Event|array $event): bool
    {
        if (is_array($event)) {
            return array_key_exists('type', $event) && $event['type'] !== null;
        }
        if ($event instanceof Event) {
            return $event->type !== null;
        }

        return false;
    }

    public function scopeDisplayUserName(): string
    {
        return $this->speaker ?? $this->bookBy->name ?? '那個不能說的人';
    }
}

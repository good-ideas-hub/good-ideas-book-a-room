<?php

namespace App\Filament\Resources\EventResource\Pages;

use App\Filament\Resources\EventResource;
use App\Models\Event;
use App\Models\Room;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;

class CreateEvent extends CreateRecord
{
    protected static string $resource = EventResource::class;

    protected function handleRecordCreation(array $data): \Illuminate\Database\Eloquent\Model
    {
        if (Event::isOverlap($data)) {
            $roomName = Room::find($data['room_id'])->name;

            Notification::make('cantBook')
                ->title('該時段 '.$roomName.' 已有預約')
                ->danger()
                ->send();

            $this->halt();
        }
        return static::getModel()::create($data);
    }
}

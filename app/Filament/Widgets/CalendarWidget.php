<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\EventResource;
use Saade\FilamentFullCalendar\Widgets\FullCalendarWidget;
use App\Models\Event;
use Illuminate\Database\Eloquent\Model;

class CalendarWidget extends FullCalendarWidget
{
    public string|null|Model $model = Event::class;

    public function config(): array
    {
        return [
            'firstDay' => 0,
            'headerToolbar' => [
                'left' => 'timeGridDay,timeGridWeek,dayGridMonth',
                'center' => 'title',
                'right' => 'prev,next today',
            ],
            'slotDuration' => '00:30:00',
        ];
    }

    public function fetchEvents(array $info): array
    {
        return Event::query()
            ->where('from', '>=', $info['start'])
            ->where('to', '<=', $info['end'])
            ->get()
            ->map(fn (Event $event) => [
                'id' => $event['id'],
                'title' => $event['name'],
                'start' => $event['from'],
                'end' => $event['to'],
            ])
            ->toArray();
    }

    protected function headerActions(): array
    {
        return [];
    }

    public function getFormSchema(): array
    {
        return EventResource::getFormSchema();
    }
}

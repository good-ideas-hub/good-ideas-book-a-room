<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\EventResource;
use Carbon\Carbon;
use Saade\FilamentFullCalendar\Widgets\FullCalendarWidget;
use App\Models\Event;
use Illuminate\Database\Eloquent\Model;
use Saade\FilamentFullCalendar\Actions;
use Filament\Forms\Form;

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

    protected function modalActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->mountUsing(function (Form $form, array $arguments) {
                    $form->fill([
                        'from' => Carbon::parse($arguments['start'])->format('Y-m-d H:i:00'),
                        'to' => Carbon::parse($arguments['end'])->format('Y-m-d H:i:00'),
                    ]);
                }),
            Actions\EditAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}

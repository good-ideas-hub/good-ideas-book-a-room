<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\EventResource;
use Carbon\Carbon;
use Saade\FilamentFullCalendar\Widgets\FullCalendarWidget;
use App\Models\Event;
use App\Models\Room;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Saade\FilamentFullCalendar\Actions;
use Filament\Forms\Form;
use \Filament\Actions\Action;

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
            ->map(function (Event $event) {
                $bookBy = User::find($event['book_by'])->name;
                $roomName = Room::find($event['room_id'])->name;
                return [
                    'id' => $event['id'],
                    'title' => $event['name']." (by $bookBy @$roomName)",
                    'start' => $event['from'],
                    'end' => $event['to'],
                    'backgroundColor' => Room::find($event['room_id'])->color,
                    'borderColor' => Room::find($event['room_id'])->color,
                ];
            })
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
                ->modalHeading('新增預約')
                ->mountUsing(function (Form $form, array $arguments) {
                    $form->fill([
                        'from' => Carbon::parse($arguments['start'])->format('Y-m-d H:i:00'),
                        'to' => Carbon::parse($arguments['end'])->format('Y-m-d H:i:00'),
                    ]);
                }),
            Actions\EditAction::make()
                ->modalHeading('編輯預約')
                ->mountUsing(function (Form $form, array $arguments, Event $event) {
                    if ($arguments) {
                        $form->fill([
                            'name' => $event->name,
                            'expected_participants' => $event->expected_participants,
                            'from' => Carbon::parse($arguments['event']['start'])->format('Y-m-d H:i:00'),
                            'to' => Carbon::parse($arguments['event']['end'])->format('Y-m-d H:i:00'),
                        ]);
                    }
                    else {
                        $form->fill([
                            'room_id' => $event->room_id,
                            'name' => $event->name,
                            'book_by' => $event->book_by,
                            'from' => $event->from,
                            'to' => $event->to,
                            'expected_participants' => $event->expected_participants,
                        ]);
                    }
                }),
            Actions\DeleteAction::make()
                ->modalHeading('刪除預約'),
        ];
    }

    protected function viewAction(): Action
    {
        return Actions\ViewAction::make()
            ->modalHeading('檢視預約');
    }
}

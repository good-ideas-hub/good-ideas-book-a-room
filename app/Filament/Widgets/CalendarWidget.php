<?php

namespace App\Filament\Widgets;

use App\Enums\EventType;
use App\Enums\WantToKnowType;
use App\Filament\Resources\EventResource;
use App\Models\Event;
use App\Models\Room;
use App\Models\User;
use App\Services\EventService;
use Carbon\Carbon;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Model;
use Saade\FilamentFullCalendar\Actions;
use Saade\FilamentFullCalendar\Widgets\FullCalendarWidget;

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
            'selectable' => ! auth()->user()->is_blocked,
            'editable' => ! auth()->user()->is_blocked,
        ];
    }

    public function fetchEvents(array $info): array
    {
        return Event::query()
            ->where('from', '>=', $info['start'])
            ->where('to', '<=', $info['end'])
            ->get()
            ->map(function (Event $event) {
                $bookBy = $event->displayUserName();
                $roomName = Room::find($event['room_id'])?->name ?? '';

                return [
                    'id' => $event['id'],
                    'title' => Event::isWantToKnow($event)
                        ? $event['name']." (by $bookBy)"
                        : $event['name']." (by $bookBy @$roomName)",
                    'start' => $event['from'],
                    'end' => $event['to'],
                    'allDay' => Carbon::parse($event['from'])->format('H:i:s') === '00:00:00'
                        && Carbon::parse($event['to'])->format('H:i:s') === '23:59:59',
                    'backgroundColor' => Room::find($event['room_id'])?->color ?? 'black',
                    'borderColor' => Room::find($event['room_id'])?->color ?? 'black',
                ];
            })
            ->toArray();
    }

    protected function headerActions(): array
    {
        return [
            Action::make('seeRooms')
                ->label('看會議室配置')
                ->modalContent(view('filament.pages.rooms'))
                ->modalCancelAction(false)
                ->modalSubmitAction(false),
        ];
    }

    private static function isBookARoom(Get $get): bool
    {
        return $get('eventType') == EventType::BookARoom->value || $get('expected_participants') !== null;
    }

    private static function isWantToKnow(Get $get): bool
    {
        return $get('eventType') == EventType::WantToKnow->value || $get('type') !== null;
    }

    public function getFormSchema(): array
    {
        return [
            // 預約空間
            Select::make('room_id')
                ->label('會議室')
                ->relationship('room', 'name')
                ->options(Room::where('is_available', 1)->pluck('name', 'id'))
                ->required()
                ->default(request()->get('room_id'))
                ->native(false)
                ->searchable()
                ->preload()
                ->visible(fn (Get $get) => static::isBookARoom($get)),
            TextInput::make('name')
                ->label('名稱')
                ->visible(fn (Get $get) => static::isBookARoom($get)),
            Select::make('book_by')
                ->label('預訂人')
                ->default(auth()->id())
                ->disabled(! auth()->user()->is_admin)
                ->relationship('bookBy', 'name')
                ->default(auth()->id())
                ->required()
                ->native(false)
                ->searchable()
                ->preload()
                ->visible(fn (Get $get) => static::isBookARoom($get)),
            DateTimePicker::make('from')
                ->label('開始時間')
                ->required()
                ->visible(fn (Get $get) => static::isBookARoom($get)),
            DateTimePicker::make('to')
                ->label('結束時間')
                ->required()
                ->visible(fn (Get $get) => static::isBookARoom($get)),
            TextInput::make('expected_participants')
                ->label('預計參與人數')
                ->numeric()
                ->minValue(0)
                ->required()
                ->visible(fn (Get $get) => static::isBookARoom($get)),

            // ＃想知道嗎
            Select::make('book_by')
                ->label('稱呼')
                ->default(auth()->id())
                ->disabled(! auth()->user()->is_admin)
                ->relationship('bookBy', 'name')
                ->default(auth()->id())
                ->required()
                ->native(false)
                ->searchable()
                ->preload()
                ->visible(fn (Get $get) => static::isWantToKnow($get)),
            DatePicker::make('date')
                ->label('日期')
                ->required()
                ->visible(fn (Get $get) => static::isWantToKnow($get)),
            Select::make('type')
                ->label('分類')
                ->options(WantToKnowType::class)
                ->required()
                ->native(false)
                ->visible(fn (Get $get) => static::isWantToKnow($get)),
            TextInput::make('name')
                ->label('題目')
                ->required()
                ->visible(fn (Get $get) => static::isWantToKnow($get)),
        ];
    }

    protected function modalActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->modalHeading('你要...？')
                ->hidden(auth()->user()->is_blocked)
                ->form(function () {
                    return [
                        Radio::make('eventType')
                            ->label('')
                            ->options(EventType::class)
                            ->required()
                            ->dehydrated(false)
                            ->live(),
                        ...static::getFormSchema(),
                    ];
                })
                ->mountUsing(function (Form $form, array $arguments) {
                    if ($arguments) {
                        $form->fill([
                            'from' => Carbon::parse($arguments['start'])->format('Y-m-d H:i:00'),
                            'to' => Carbon::parse($arguments['end'])->format('Y-m-d H:i:00'),
                        ]);
                    }
                    $form->fill();
                })
                ->using(function (CreateAction $action, array $data): Event {
                    if (Event::isOverlap($data)) {
                        $roomName = Room::find($data['room_id'])->name;

                        Notification::make('cantBook')
                            ->title('該時段 '.$roomName.' 已有預約')
                            ->danger()
                            ->send();

                        $action->halt();
                    }

                    if (Event::isWantToKnow($data)) {
                        $newEvent = Event::create([
                            ...$data,
                            'from' => Carbon::parse($data['date'])->startOfDay()->format('Y-m-d H:i:s'),
                            'to' => Carbon::parse($data['date'])->endOfDay()->format('Y-m-d H:i:s'),
                        ]);

                        if ($newEvent) {
                            EventService::sendNewEventNotificationToSlack($newEvent);
                        }

                        return $newEvent;
                    }

                    return Event::create($data);
                }),
        ];
    }

    protected function viewAction(): Action
    {
        return Actions\ViewAction::make()
            ->modalHeading('檢視預約')
            ->mountUsing(function (Form $form, array $arguments, Event $event) {
                $form->fill([
                    'room_id' => $event->room_id,
                    'name' => $event->name,
                    'book_by' => $event->speaker ?? $event->book_by,
                    'date' => $event->from,
                    'from' => $event->from,
                    'to' => $event->to,
                    'expected_participants' => $event->expected_participants,
                    'type' => $event->type,
                ]);
            })
            ->modalFooterActions([
                Actions\EditAction::make()
                    ->modalHeading('編輯預約')
                    ->hidden(fn (Event $record) => ! EventResource::canEdit($record) || $record->speaker)
                    ->mountUsing(function (Form $form, array $arguments, Event $event) {
                        $form->fill([
                            'room_id' => $event->room_id,
                            'name' => $event->name,
                            'book_by' => $event->book_by,
                            'date' => $event->from,
                            'from' => $event->from,
                            'to' => $event->to,
                            'expected_participants' => $event->expected_participants,
                            'type' => $event->type,
                        ]);
                    })
                    ->using(function (EditAction $action, Event $record, array $data): Event {
                        if (Event::isOverlap($data, $record)) {
                            $roomName = Room::find($data['room_id'])->name;

                            Notification::make('cantBook')
                                ->title('該時段 '.$roomName.' 已有預約')
                                ->danger()
                                ->send();

                            $action->halt();
                        }

                        if (Event::isWantToKnow($data)) {
                            $record->update([
                                'name' => $data['name'],
                                'book_by' => $data['book_by'],
                                'from' => Carbon::parse($data['date'])->startOfDay()->format('Y-m-d H:i:s'),
                                'to' => Carbon::parse($data['date'])->endOfDay()->format('Y-m-d H:i:s'),
                                'type' => $data['type'],
                            ]);
                        }

                        $record->update($data);

                        return $record;
                    }),
                Actions\DeleteAction::make()
                    ->modalHeading('刪除預約')
                    ->hidden(fn (Event $record) => ! EventResource::canEdit($record)),
            ]);
    }
}

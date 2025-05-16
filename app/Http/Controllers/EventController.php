<?php

namespace App\Http\Controllers;

use App\Enums\WantToKnowType;
use App\Models\Event;
use App\Models\User;
use App\Services\EventService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules\Enum;
use Illuminate\Validation\ValidationException;

class EventController extends Controller
{
    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'speaker' => 'required|string|max:255',
                'date' => ['nullable', 'sometimes', 'string', 'date'],
                'from' => ['nullable', 'sometimes', 'string', 'date_format:Y-m-d H:i:s'],
                'to' => ['nullable', 'sometimes', 'string', 'date_format:Y-m-d H:i:s'],
                'type' => ['nullable', 'sometimes', new Enum(WantToKnowType::class)],
                'name' => 'required|string|max:255',
                'room_id' => ['nullable', 'sometimes', 'integer'],
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'error' => 'Invalid input.',
                'details' => $e->errors(),
            ], 400);
        }

        try {
            $newEvent = Event::create(array_merge($validatedData, [
                'book_by' => User::where('name', 'admin')->first()->id,
                'from' => isset($validatedData['from']) ? $validatedData['from'] : Carbon::parse($validatedData['date'])->format('Y-m-d 00:00:00'),
                'to' => isset($validatedData['to']) ? $validatedData['to'] : Carbon::parse($validatedData['date'])->format('Y-m-d 00:00:00'),
            ]));

            if (Event::isWantToKnow($validatedData)) {
                EventService::sendNewEventNotificationToSlack($newEvent);
            } else {
                EventService::sendBookARoomNotificationToSlack($newEvent);
            }
        } catch (\Exception $e) {
            return response()->json([
                'error' => '建立活動失敗',
                'message' => $e->getMessage()
            ], 500);
        }

        return response()->json([
            'message' => 'Event created successfully!',
            'data' => $newEvent,
        ], 201);
    }
}

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
                'date' => 'required|string|date',
                'type' => ['required', new Enum(WantToKnowType::class)],
                'name' => 'required|string|max:255',
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'error' => 'Invalid input.',
                'details' => $e->errors(),
            ], 400);
        }

        $newEvent = Event::create([
            ...$validatedData,
            'book_by' => User::where('name', 'admin')->first()->id,
            'from' => Carbon::parse($validatedData['date'])->format('Y-m-d 00:00:00'),
            'to' => Carbon::parse($validatedData['date'])->format('Y-m-d 00:00:00'),
        ]);

        EventService::sendNewEventNotificationToSlack($newEvent);

        return response()->json([
            'message' => 'Event created successfully!',
            'data' => $newEvent,
        ], 201);
    }
}

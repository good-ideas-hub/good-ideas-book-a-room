<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\EventController;
use App\Models\Event;
use App\Models\Room;
use App\Models\User;
use App\Services\EventService;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Route;

Route::post('/events', [EventController::class, 'store']);

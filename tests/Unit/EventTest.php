<?php

namespace Tests\Unit;

use App\Models\Event;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

beforeEach(function () {
    Event::create([
        'book_by' => 1,
        'room_id' => 1,
        'from' => '3025-02-10 08:00:00',
        'to' => '3025-02-10 19:00:00',
    ]);
});

test('events overlap', function () {
    $data = [
        'from' => '3025-02-10 08:00:00',
        'to' => '3025-02-10 19:00:00',
        'room_id' => 1,
    ];
    expect(Event::isOverlap($data))->toBeTrue();
});

test('events not overlap: from is same with existed to', function () {
    $data = [
        'from' => '3025-02-10 19:00:00',
        'to' => '3025-02-10 20:00:00',
        'room_id' => 1,
    ];
    expect(Event::isOverlap($data))->toBeFalse();
});

test('events not overlap: same from and to, different room_id', function () {
    $data = [
        'from' => '3025-02-10 08:00:00',
        'to' => '3025-02-10 19:00:00',
        'room_id' => 2,
    ];
    expect(Event::isOverlap($data))->toBeFalse();
});

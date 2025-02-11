<?php

use Illuminate\Support\Facades\DB;

it('environment:testing', function () {
    expect(env('APP_ENV'))->toBe('testing')
        ->and(env('DB_CONNECTION'))->toBe('mysql');
});

it('checks database connection', function () {
    $this->assertNotNull(DB::connection()->getPdo());
});

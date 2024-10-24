<?php

use Illuminate\Support\Facades\Route;
use Laravel\Socialite\Facades\Socialite;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/auth/redirect/slack', function () {
    return Socialite::driver('slack')->redirect();
});

Route::get('/auth/callback/slack', function () {
    $slackUser = Socialite::driver('slack')->user();

    $user = User::firstOrCreate([
        'email' => $slackUser->email,
    ], [
        'slack_id' => $slackUser->id,
        'name' => $slackUser->name,
        'password' => Hash::make($slackUser->email),
    ]);

    Auth::login($user);

    return redirect('/admin');
});

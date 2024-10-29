<?php

use App\Http\Middleware\SlackTokenRedirect;
use Illuminate\Support\Facades\Route;
use Laravel\Socialite\Facades\Socialite;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

Route::get('/', function () {
    return view('welcome');
})->middleware(SlackTokenRedirect::class);

Route::get('/admin/login', function () {
    if (config('app.env') == 'production') {
        return redirect('/');
    }
    return redirect('/admin/login');
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
        'slack_token' => $slackUser->token,
        'name' => $slackUser->name,
        'password' => Hash::make($slackUser->email),
    ]);

    Auth::login($user);

    return redirect('/admin');
});

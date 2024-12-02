<?php

use App\Http\Middleware\SlackTokenRedirect;
use Illuminate\Support\Facades\Route;
use Laravel\Socialite\Facades\Socialite;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

//Route::get('/test-trigger-error', function () {
//    throw new Error('MSG');
//});

Route::get('/', function () {
    return view('welcome');
})->middleware(SlackTokenRedirect::class);

Route::redirect('/login', '/')->name('login');

Route::get('/auth/redirect/slack', function () {
    return Socialite::driver('slack')->redirect();
});

Route::get('/auth/callback/slack', function () {
    $slackUser = Socialite::driver('slack')->user();

    $user = User::where('slack_id', $slackUser->getId())
        ->orwhere('email', $slackUser->getEmail())
        ->first();

    if ($user) {
        if (empty($user->slack_token)) {
            $user->slack_token = $slackUser->token;
            $user->save();
        }
    } else {
        $user = User::create([
            'slack_id' => $slackUser->id,
            'slack_token' => $slackUser->token,
            'name' => $slackUser->name,
            'email' => $slackUser->email,
            'password' => Hash::make($slackUser->email),
        ]);
    }

    Auth::login($user);

    return redirect('/admin');
});

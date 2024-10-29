<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class SlackTokenRedirect
{
    public function handle($request, Closure $next)
    {
        $user = Auth::user();

        if ($user && $user->slack_token) {
            return redirect('/admin');
        }

        return $next($request);
    }
}


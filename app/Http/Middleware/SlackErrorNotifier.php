<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Symfony\Component\HttpFoundation\Response;

class SlackErrorNotifier
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if ($response->status() >= 400 && $response->status() < 600) {
            $this->sendErrorToSlack($request, $response);
        }

        return $response;
    }

    protected function sendErrorToSlack($request, $response): void
    {
        $slackWebhookUrl = config('services.slack.webhook_url');
        $statusCode = $response->status();
        $fullUrl = $request->fullUrl();
        $message = $response->exception->getMessage();

        Http::post($slackWebhookUrl, [
            'env' => config('app.env'),
            'text' => "Error occurred:\nStatus Code: {$statusCode}\nURL: {$fullUrl}\nMessage: {$message}]}",
        ]);
    }
}

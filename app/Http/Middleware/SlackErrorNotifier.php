<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Symfony\Component\HttpFoundation\Response;

class SlackErrorNotifier
{
    /**
     * @throws ConnectionException
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if ($response->getStatusCode() >= 500) {
            $response->dt = date('Y-m-d H:i:s');

            if (config('app.env') === 'production') {
                $this->sendErrorToSlack($request, $response);
            }
        }

        return $response;
    }

    protected function logErrorMsg($filename, $content): void
    {
        file_put_contents("../storage/logs/{$filename}.txt", $content);
    }

    /**
     * @throws ConnectionException
     */
    protected function sendErrorToSlack($request, $response): void
    {
        $user = auth()->user();
        $statusCode = $response->getStatusCode();
        $fullUrl = $request->fullUrl();
        $errorMessage = $response->exception->getMessage();
        $traceString = $response->exception->getTraceAsString();
        $timestamp = now()->setTimezone('Asia/Taipei')->format('Y-m-d_H-i-s');
        $filename = "stack_trace_{$timestamp}.txt";
        $filePath = storage_path("logs/{$filename}");

        // Save stack trace to a temporary file
        file_put_contents($filePath, $traceString);

        // Step 1: Get an upload URL
        $uploadUrlResponse = Http::withToken(config('services.slack.bot_token'))
            ->asForm()
            ->post('https://slack.com/api/files.getUploadURLExternal', [
                'filename' => $filename,
                'length' => filesize($filePath),
            ]);

        if (! $uploadUrlResponse->ok()) {
            // Handle error
            return;
        }

        $uploadUrlData = $uploadUrlResponse->json();
        $uploadUrl = $uploadUrlData['upload_url'];
        $fileId = $uploadUrlData['file_id'];

        // Step 2: Upload the file to the provided URL
        $fileUploadResponse = Http::attach(
            'file', file_get_contents($filePath), $filename
        )->post($uploadUrl);

        if ($fileUploadResponse->failed()) {
            // Handle error
            return;
        }

        // Step 3: Complete the upload and share the file
        $getStatusCodeEmoji = function ($host) {
            if (config('app.env') === 'local') {
                return 'large_yellow_circle';
            } elseif ($host.startsWith('staging')) {
                return 'large_orange_circle';
            } else {
                return 'alert';
            }
        };
        $completeUploadResponse = Http::withToken(config('services.slack.bot_token'))
            ->asForm()
            ->post('https://slack.com/api/files.completeUploadExternal', [
                'files' => json_encode([
                    [
                        'id' => $fileId,
                        'title' => $filename,
                    ],
                ]),
                'channel_id' => config('services.slack.channel_id'),
                'initial_comment' => "Status code: :{$getStatusCodeEmoji($request->host)}: {$statusCode}\nDatetime: {$response->dt}\nUser: ".($user ? $user->id : 'Guest')."\nFull URL: {$fullUrl}"."\nError message: `{$errorMessage}`",
            ]);

        if (! $completeUploadResponse->ok()) {
            // Handle error
            return;
        }

        // Optionally, delete the temporary file after uploading
        unlink($filePath);
    }
}

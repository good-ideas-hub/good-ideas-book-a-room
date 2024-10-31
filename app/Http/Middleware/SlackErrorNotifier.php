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

        if ($response->status() >= 500) {
            $response->dt = date('Y-m-d H:i:s');

            $this->sendErrorToSlack($request, $response);
            $this->logErrorMsg(
                $response->dt,
                $response->exception->getTraceAsString()
            );
        }

        return $response;
    }

    protected function logErrorMsg($filename, $content): void
    {
        file_put_contents("../storage/logs/{$filename}.txt", $content);
    }

    protected function sendErrorToSlack($request, $response): void
    {
        $user = auth()->user();
        $slackWebhookUrl = config('services.slack.webhook_url');
        $statusCode = $response->status();
        $fullUrl = $request->fullUrl();
        $traceString = $response->exception->getTraceAsString();
        $chunks = str_split($traceString, 2400);

        Http::post($slackWebhookUrl, [
            'token' => config('services.slack.bot_token'),
            'channel' => config('services.slack.channel_id'),
            'blocks' => [
                [
                    'type' => 'section',
                    'text' => [
                        'type' => 'mrkdwn',
                        'text' => "status code: :red_circle: {$statusCode}\ndatetime: {$response->dt}\n"."user: ".($user ? $user->id : '')."\nfull url: {$fullUrl}\n```\n$chunks[0]\n```",
                    ]
                ],
            ]
        ]);
    }


    /*
     * steps for sending error stack file to slack
     * 1. receive error
     * 2. create file in storage/logs
     * 3. upload file to slack
     * 4. send error message + file to book-a-room-err channel
     * */

    /*
     * issues:
     * - permelink 開起來有問題
     * - 傳 file 到 slack channel 沒成功
     * ref. https://api.slack.com/methods/files.getUploadURLExternal
     * */
    protected function sendErrorToSlackWithStack($request, $response): void
    {
        $slackWebhookUrl = config('services.slack.webhook_url');
        $statusCode = $response->status();
        $fullUrl = $request->fullUrl();
        $traceString = $response->exception->getTraceAsString();
        $datetime = date('Y-m-d_His');
        file_put_contents("../storage/logs/{$datetime}.txt", $traceString);

        $response = $this->upload($datetime, $traceString);

        $res = Http::post($slackWebhookUrl, [
            'type' => 'file',
            'external_id' => \Str::uuid(),
            'source' => $response['files'][0]['permalink'],
        ]);
        dd($res);
//        if ($res->ok()) {
//            // delete file
//        }
    }

        private function upload($filename, $content)
        {
            $getUploadURLExternal = Http::withToken(config('services.slack.bot_token'))
                ->attach('file', $content, "{$filename}.txt")
                ->post('https://slack.com/api/files.getUploadURLExternal', [
                    'filename' => $filename,
                    'length' => filesize("../storage/logs/{$filename}.txt"),
                ]);
            $completeUploadExternal = Http::withToken(config('services.slack.bot_token'))
                ->post('https://slack.com/api/files.completeUploadExternal', [
                    'files' => [
                        [
                            'title' => $filename,
                            'id' => $getUploadURLExternal['file_id']
                        ]
                    ]
                ]);
            return $completeUploadExternal->json();
        }
}

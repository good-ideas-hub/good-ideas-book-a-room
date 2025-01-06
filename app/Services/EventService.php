<?php

namespace App\Services;

use App\Models\Event;
use Illuminate\Support\Facades\Http;

class EventService
{
    public static function sendNewEventNotificationToSlack(Event $event): void
    {
        Http::withToken(config('services.slack.bot_token'))
            ->asJson()
            ->post('https://slack.com/api/chat.postMessage', [
                'channel' => config('services.slack.want_to_know_channel_id'),
                'blocks' => [
                    [
                        'type' => 'section',
                        'text' => [
                            'type' => 'mrkdwn',
                            'text' => '稱呼： '.($event->displayUserName())."\n日期： ".\Carbon\Carbon::parse($event->from)->format('Y/m/d')."\n類別： {$event->type->getLabel()}\n題目： {$event->name}",
                        ],
                    ],
                ],
            ]);
    }
}

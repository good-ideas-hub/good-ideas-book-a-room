<?php

namespace App\Services;

use App\Models\Event;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Client\Response;

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
                            'text' => '稱呼： '.($event->displayUserName())."\n日期： ".\Carbon\Carbon::parse($event->from)->format('Y/m/d')."\n類別： ".($event->type ? $event->type->getLabel() : '一般活動')."\n題目： {$event->name}",
                        ],
                    ],
                ],
            ]);
    }

    public static function sendBookARoomNotificationToSlack(Event $event): Response
    {
        $room = $event->room;
        $bookBy = $event->bookBy;
        $name = $bookBy->name == 'admin' ? 'Google 表單' : $bookBy->name;
        
        // 直接定義頻道 ID
        $channelId = 'C08SDGAJYHM';
        
        return Http::withToken(config('services.slack.bot_token'))
            ->asJson()
            ->post('https://slack.com/api/chat.postMessage', [
                'channel' => $channelId,
                'blocks' => [
                    [
                        'type' => 'section',
                        'text' => [
                            'type' => 'mrkdwn',
                            'text' => "*會議室預定*\n{$event->name}\n{$room->name} by {$name}\n".\Carbon\Carbon::parse($event->from)->format('Y/m/d H:i')." ~ ".\Carbon\Carbon::parse($event->to)->format('Y/m/d H:i'),
                        ],
                    ],
                ],
            ]);
    }

    public static function sendUpdatedEventNotificationToSlack(Event $event): void
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
                            'text' => '*更新活動*'."\n稱呼： ".($event->displayUserName())."\n日期： ".\Carbon\Carbon::parse($event->from)->format('Y/m/d')."\n類別： ".($event->type ? $event->type->getLabel() : '一般活動')."\n題目： {$event->name}",
                        ],
                    ],
                ],
            ]);
    }

    public static function sendUpdatedBookARoomNotificationToSlack(Event $event): Response
    {
        $room = $event->room;
        $bookBy = $event->bookBy;
        $name = $bookBy->name == 'admin' ? 'Google 表單' : $bookBy->name;
        
        // 直接定義頻道 ID
        $channelId = 'C08SDGAJYHM';
        
        return Http::withToken(config('services.slack.bot_token'))
            ->asJson()
            ->post('https://slack.com/api/chat.postMessage', [
                'channel' => $channelId,
                'blocks' => [
                    [
                        'type' => 'section',
                        'text' => [
                            'type' => 'mrkdwn',
                            // 'text' => "*已更新會議室預定*\n會議室： {$room->name}\n預定人： {$name}"."\n名稱： {$event->name}"."\n開始時間： ".\Carbon\Carbon::parse($event->from)->format('Y/m/d H:i')."\n結束時間： ".\Carbon\Carbon::parse($event->to)->format('Y/m/d H:i'),
                            'text' => "*更新預定*\n{$event->name}\n{$room->name} by {$name}\n".\Carbon\Carbon::parse($event->from)->format('Y/m/d H:i')." ~ ".\Carbon\Carbon::parse($event->to)->format('Y/m/d H:i'),
                        ],
                    ],
                ],
            ]);
    }
}

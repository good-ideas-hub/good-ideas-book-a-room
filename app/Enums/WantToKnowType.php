<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum WantToKnowType: int implements HasLabel
{
    case Project = 1;
    case Learn = 2;
    case Develop = 3;
    case Career = 4;
    case Life = 5;

    public function getLabel(): ?string
    {
        return match ($this) {
            self::Project => '專案經驗（踩雷經驗、溝通之術）',
            self::Learn => '學習小心得',
            self::Develop => '技術剖析',
            self::Career => '職場工作、面試經驗談',
            self::Life => '生活頻道（各種跟技術無關的，旅遊、吃的、喝的、育兒...）',
            default => null,
        };
    }
}

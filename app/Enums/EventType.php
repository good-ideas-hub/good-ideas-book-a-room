<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum EventType: int implements HasLabel
{
    case BookARoom = 1;
    case WantToKnow = 2;

    public function getLabel(): ?string
    {
        return match ($this) {
            self::BookARoom => '預約空間',
            self::WantToKnow => '＃想知道嗎'
        };
    }
}

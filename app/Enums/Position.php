<?php

namespace App\Enums;

enum Position: string
{
    case Goalkeeper = 'goalkeeper';
    case Defender = 'defender';
    case Midfielder = 'midfielder';
    case Attacker = 'attacker';

    public function defaultCount(): int
    {
        return match ($this) {
            self::Goalkeeper => 3,
            self::Defender => 6,
            self::Midfielder => 6,
            self::Attacker => 5,
        };
    }
}

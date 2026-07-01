<?php

namespace App\Policies;

use App\Models\Player;
use App\Models\User;

class PlayerPolicy
{
    public function update(User $user, Player $player): bool
    {
        return $user->id === $player->team->user_id;
    }
}

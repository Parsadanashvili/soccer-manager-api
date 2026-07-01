<?php

namespace App\Policies;

use App\Models\Player;
use App\Models\TransferList;
use App\Models\User;

class TransferListPolicy
{
    /**
     * Determine whether the user can create models.
     */
    public function create(User $user, Player $player): bool
    {
        return $user->id === $player->team->user_id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, TransferList $transferList): bool
    {
        return $user->id === $transferList->player->team->user_id;
    }
}

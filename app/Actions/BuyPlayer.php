<?php

namespace App\Actions;

use App\Models\Player;
use App\Models\Transfer;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class BuyPlayer
{
    public function handle(User $buyer, Player $player): Player
    {
        $buyerTeam = $buyer->team()->firstOrFail();
        $listing = $player->transferListing;

        if ($listing === null) {
            throw ValidationException::withMessages([
                'player' => __('market.not_listed'),
            ]);
        }

        if ($player->team_id === $buyerTeam->id) {
            throw ValidationException::withMessages([
                'player' => __('market.own_player'),
            ]);
        }

        $price = $listing->asking_price;

        if ($buyerTeam->budget < $price) {
            throw ValidationException::withMessages([
                'player' => __('market.insufficient_funds'),
            ]);
        }

        return DB::transaction(function () use ($buyerTeam, $player, $listing, $price): Player {
            $sellerTeam = $player->team()->firstOrFail();

            $buyerTeam->decrement('budget', $price);
            $sellerTeam->increment('budget', $price);

            $player->update([
                'team_id' => $buyerTeam->id,
                'market_value' => $this->appreciatedValue($player->market_value),
            ]);

            $listing->delete();

            Transfer::create([
                'player_id' => $player->id,
                'from_team_id' => $sellerTeam->id,
                'to_team_id' => $buyerTeam->id,
                'price' => $price,
            ]);

            return $player;
        });
    }


    private function appreciatedValue(int $current): int
    {
        return (int) round($current * (100 + random_int(10, 100)) / 100);
    }
}

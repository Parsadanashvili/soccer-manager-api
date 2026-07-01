<?php

namespace Database\Factories;

use App\Models\Player;
use App\Models\Team;
use App\Models\Transfer;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Transfer>
 */
class TransferFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'player_id' => Player::factory(),
            'from_team_id' => Team::factory(),
            'to_team_id' => Team::factory(),
            'price' => fake()->numberBetween(1_000_000, 5_000_000),
        ];
    }
}

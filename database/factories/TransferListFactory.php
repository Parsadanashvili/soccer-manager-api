<?php

namespace Database\Factories;

use App\Models\Player;
use App\Models\TransferList;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TransferList>
 */
class TransferListFactory extends Factory
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
            'asking_price' => fake()->numberBetween(1_000_000, 5_000_000),
        ];
    }
}

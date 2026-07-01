<?php

namespace Database\Factories;

use App\Enums\Position;
use App\Models\Player;
use App\Models\Team;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Player>
 */
class PlayerFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'team_id' => Team::factory(),
            'first_name' => fake()->firstName(),
            'last_name' => fake()->lastName(),
            'country' => fake()->country(),
            'age' => fake()->numberBetween(18, 40),
            'position' => fake()->randomElement(Position::cases()),
            'market_value' => 1_000_000,
        ];
    }

    public function position(Position $position): static
    {
        return $this->state(fn(array $attributes) => [
            'position' => $position,
        ]);
    }
}

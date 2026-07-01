<?php

namespace App\Actions;

use App\Enums\Position;
use App\Models\Team;
use App\Models\User;
use Illuminate\Support\Facades\DB;


class GenerateTeam
{
    private const INITIAL_BUDGET = 5_000_000;

    private const INITIAL_PLAYER_VALUE = 1_000_000;

    /**
     * @var list<string>
     */
    private const CLUB_SUFFIXES = ['FC', 'United', 'City'];


    public function handle(User $user): Team
    {
        return DB::transaction(function () use ($user) {
            $team = $user->team()->create([
                'name' => $this->randomTeamName(),
                'country' => fake()->country(),
                'budget' => self::INITIAL_BUDGET,
            ]);

            $team->players()->createMany($this->buildSquad());

            return $team;
        });
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function buildSquad(): array
    {
        $players = [];

        foreach (Position::cases() as $position) {
            for ($i = 0; $i < $position->defaultCount(); $i++) {
                $players[] = [
                    'first_name' => fake()->firstName(),
                    'last_name' => fake()->lastName(),
                    'country' => fake()->country(),
                    'age' => fake()->numberBetween(18, 40),
                    'position' => $position->value,
                    'market_value' => self::INITIAL_PLAYER_VALUE,
                ];
            }
        }

        return $players;
    }

    private function randomTeamName(): string
    {
        return fake()->city() . ' ' . fake()->randomElement(self::CLUB_SUFFIXES);
    }
}

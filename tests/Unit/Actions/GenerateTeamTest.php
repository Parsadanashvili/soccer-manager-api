<?php

namespace Tests\Unit\Actions;

use App\Actions\GenerateTeam;
use App\Enums\Position;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GenerateTeamTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_creates_a_team_with_the_starting_budget(): void
    {
        $user = User::factory()->create();

        $team = (new GenerateTeam())->handle($user);

        $this->assertSame($user->id, $team->user_id);
        $this->assertSame(5_000_000, $team->budget);
    }

    public function test_it_generates_twenty_players_each_valued_at_one_million(): void
    {
        $team = (new GenerateTeam())->handle(User::factory()->create());

        $players = $team->players;

        $this->assertCount(20, $players);
        $this->assertTrue($players->every(fn ($player) => $player->market_value === 1_000_000));
        $this->assertSame(20_000_000, $team->value);
    }

    public function test_it_respects_the_required_position_distribution(): void
    {
        $players = (new GenerateTeam())->handle(User::factory()->create())->players;

        $this->assertSame(3, $players->where('position', Position::Goalkeeper)->count());
        $this->assertSame(6, $players->where('position', Position::Defender)->count());
        $this->assertSame(6, $players->where('position', Position::Midfielder)->count());
        $this->assertSame(5, $players->where('position', Position::Attacker)->count());
    }

    public function test_each_player_has_an_age_within_the_allowed_range(): void
    {
        $players = (new GenerateTeam())->handle(User::factory()->create())->players;

        foreach ($players as $player) {
            $this->assertGreaterThanOrEqual(18, $player->age);
            $this->assertLessThanOrEqual(40, $player->age);
        }
    }
}

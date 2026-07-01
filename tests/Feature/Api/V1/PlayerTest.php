<?php

namespace Tests\Feature\Api\V1;

use App\Models\Player;
use App\Models\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class PlayerTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_user_can_list_only_their_own_players(): void
    {
        $user = User::factory()->create();
        $team = Team::factory()->for($user)->create();
        Player::factory()->count(4)->for($team)->create();

        // Another team's players must not appear.
        Player::factory()->count(2)->create();

        Sanctum::actingAs($user);

        $this->getJson('/api/v1/players')
            ->assertOk()
            ->assertJsonCount(4);
    }

    public function test_a_user_can_update_their_own_player(): void
    {
        $user = User::factory()->create();
        $team = Team::factory()->for($user)->create();
        $player = Player::factory()->for($team)->create();

        Sanctum::actingAs($user);

        $this->patchJson("/api/v1/players/{$player->id}", [
            'first_name' => 'Khvicha',
            'last_name' => 'Kvaratskhelia',
            'country' => 'Georgia',
        ])
            ->assertOk()
            ->assertJsonPath('first_name', 'Khvicha')
            ->assertJsonPath('country', 'Georgia');

        $this->assertDatabaseHas('players', [
            'id' => $player->id,
            'first_name' => 'Khvicha',
            'country' => 'Georgia',
        ]);
    }

    public function test_a_user_cannot_update_another_teams_player(): void
    {
        $user = User::factory()->create();
        Team::factory()->for($user)->create();

        $foreignPlayer = Player::factory()->create();

        Sanctum::actingAs($user);

        $this->patchJson("/api/v1/players/{$foreignPlayer->id}", [
            'country' => 'Georgia',
        ])->assertForbidden();

        $this->assertDatabaseHas('players', [
            'id' => $foreignPlayer->id,
            'country' => $foreignPlayer->country,
        ]);
    }

    public function test_listing_players_requires_authentication(): void
    {
        $this->getJson('/api/v1/players')->assertUnauthorized();
    }
}

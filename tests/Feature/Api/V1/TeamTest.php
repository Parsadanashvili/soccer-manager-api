<?php

namespace Tests\Feature\Api\V1;

use App\Models\Player;
use App\Models\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class TeamTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_user_can_view_their_team_with_players(): void
    {
        $user = User::factory()->create();
        $team = Team::factory()->for($user)->create();
        Player::factory()->count(3)->for($team)->create();

        Sanctum::actingAs($user);

        $this->getJson('/api/v1/team')
            ->assertOk()
            ->assertJsonPath('id', $team->id)
            ->assertJsonPath('value', 3_000_000)
            ->assertJsonCount(3, 'players');
    }

    public function test_a_user_can_update_their_team_name_and_country(): void
    {
        $user = User::factory()->create();
        $team = Team::factory()->for($user)->create();

        Sanctum::actingAs($user);

        $this->patchJson('/api/v1/team', [
            'name' => 'Dinamo Tbilisi',
            'country' => 'Georgia',
        ])
            ->assertOk()
            ->assertJsonPath('name', 'Dinamo Tbilisi')
            ->assertJsonPath('country', 'Georgia');

        $this->assertDatabaseHas('teams', [
            'id' => $team->id,
            'name' => 'Dinamo Tbilisi',
            'country' => 'Georgia',
        ]);
    }

    public function test_team_update_rejects_a_blank_name(): void
    {
        $user = User::factory()->create();
        Team::factory()->for($user)->create();

        Sanctum::actingAs($user);

        $this->patchJson('/api/v1/team', ['name' => ''])
            ->assertUnprocessable()
            ->assertJsonValidationErrorFor('name');
    }

    public function test_viewing_a_team_requires_authentication(): void
    {
        $this->getJson('/api/v1/team')->assertUnauthorized();
    }
}

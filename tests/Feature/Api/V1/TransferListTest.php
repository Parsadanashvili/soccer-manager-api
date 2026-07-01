<?php

namespace Tests\Feature\Api\V1;

use App\Models\Player;
use App\Models\Team;
use App\Models\TransferList;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class TransferListTest extends TestCase
{
    use RefreshDatabase;

    public function test_an_owner_can_list_their_player_for_transfer(): void
    {
        $user = User::factory()->create();
        $team = Team::factory()->for($user)->create();
        $player = Player::factory()->for($team)->create();

        Sanctum::actingAs($user);

        $this->postJson("/api/v1/players/{$player->id}/transfer-list", [
            'asking_price' => 2_500_000,
        ])
            ->assertCreated()
            ->assertJsonPath('asking_price', 2_500_000)
            ->assertJsonPath('player.id', $player->id);

        $this->assertDatabaseHas('transfer_lists', [
            'player_id' => $player->id,
            'asking_price' => 2_500_000,
        ]);
    }

    public function test_a_player_cannot_be_listed_twice(): void
    {
        $user = User::factory()->create();
        $team = Team::factory()->for($user)->create();
        $player = Player::factory()->for($team)->create();
        TransferList::factory()->for($player)->create();

        Sanctum::actingAs($user);

        $this->postJson("/api/v1/players/{$player->id}/transfer-list", [
            'asking_price' => 2_500_000,
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrorFor('player');

        $this->assertDatabaseCount('transfer_lists', 1);
    }

    public function test_a_user_cannot_list_another_teams_player(): void
    {
        $user = User::factory()->create();
        Team::factory()->for($user)->create();
        $foreignPlayer = Player::factory()->create();

        Sanctum::actingAs($user);

        $this->postJson("/api/v1/players/{$foreignPlayer->id}/transfer-list", [
            'asking_price' => 2_500_000,
        ])->assertForbidden();

        $this->assertDatabaseCount('transfer_lists', 0);
    }

    public function test_the_asking_price_must_be_a_positive_integer(): void
    {
        $user = User::factory()->create();
        $team = Team::factory()->for($user)->create();
        $player = Player::factory()->for($team)->create();

        Sanctum::actingAs($user);

        $this->postJson("/api/v1/players/{$player->id}/transfer-list", [
            'asking_price' => 0,
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrorFor('asking_price');
    }

    public function test_an_owner_can_remove_their_listing(): void
    {
        $user = User::factory()->create();
        $team = Team::factory()->for($user)->create();
        $player = Player::factory()->for($team)->create();
        $listing = TransferList::factory()->for($player)->create();

        Sanctum::actingAs($user);

        $this->deleteJson("/api/v1/transfer-list/{$listing->id}")
            ->assertNoContent();

        $this->assertDatabaseMissing('transfer_lists', ['id' => $listing->id]);
    }

    public function test_a_user_cannot_remove_another_teams_listing(): void
    {
        $user = User::factory()->create();
        Team::factory()->for($user)->create();

        $foreignPlayer = Player::factory()->create();
        $listing = TransferList::factory()->for($foreignPlayer)->create();

        Sanctum::actingAs($user);

        $this->deleteJson("/api/v1/transfer-list/{$listing->id}")
            ->assertForbidden();

        $this->assertDatabaseHas('transfer_lists', ['id' => $listing->id]);
    }
}

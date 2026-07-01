<?php

namespace Tests\Feature\Api\V1;

use App\Models\Player;
use App\Models\Team;
use App\Models\TransferList;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class MarketTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_market_lists_every_transfer_listed_player(): void
    {
        TransferList::factory()->count(3)->create();

        Sanctum::actingAs(User::factory()->create());

        $this->getJson('/api/v1/market')
            ->assertOk()
            ->assertJsonCount(3)
            ->assertJsonStructure([['id', 'asking_price', 'player' => ['id', 'full_name']]]);
    }

    public function test_a_user_can_buy_a_listed_player(): void
    {
        [$buyer, $buyerTeam] = $this->userWithTeam(['budget' => 5_000_000]);
        [$seller, $sellerTeam] = $this->userWithTeam(['budget' => 5_000_000]);

        $player = Player::factory()->for($sellerTeam)->create(['market_value' => 1_000_000]);
        $listing = TransferList::factory()->for($player)->create(['asking_price' => 2_000_000]);

        Sanctum::actingAs($buyer);

        $response = $this->postJson("/api/v1/market/{$player->id}/buy")
            ->assertOk()
            ->assertJsonPath('id', $player->id)
            ->assertJsonPath('team.id', $buyerTeam->id);

        // Ownership transferred.
        $this->assertDatabaseHas('players', [
            'id' => $player->id,
            'team_id' => $buyerTeam->id,
        ]);

        // Budgets moved by the asking price.
        $this->assertDatabaseHas('teams', ['id' => $buyerTeam->id, 'budget' => 3_000_000]);
        $this->assertDatabaseHas('teams', ['id' => $sellerTeam->id, 'budget' => 7_000_000]);

        // Value appreciated 10%-100%.
        $newValue = $response->json('market_value');
        $this->assertGreaterThanOrEqual(1_100_000, $newValue);
        $this->assertLessThanOrEqual(2_000_000, $newValue);

        // Listing consumed and transfer recorded.
        $this->assertDatabaseMissing('transfer_lists', ['id' => $listing->id]);
        $this->assertDatabaseHas('transfers', [
            'player_id' => $player->id,
            'from_team_id' => $sellerTeam->id,
            'to_team_id' => $buyerTeam->id,
            'price' => 2_000_000,
        ]);
    }

    public function test_a_user_cannot_buy_their_own_player(): void
    {
        [$user, $team] = $this->userWithTeam();
        $player = Player::factory()->for($team)->create();
        TransferList::factory()->for($player)->create(['asking_price' => 1_000_000]);

        Sanctum::actingAs($user);

        $this->postJson("/api/v1/market/{$player->id}/buy")
            ->assertUnprocessable()
            ->assertJsonPath('message', __('market.own_player'));
    }

    public function test_a_user_cannot_buy_a_player_that_is_not_listed(): void
    {
        [$user] = $this->userWithTeam();
        $player = Player::factory()->create();

        Sanctum::actingAs($user);

        $this->postJson("/api/v1/market/{$player->id}/buy")
            ->assertUnprocessable()
            ->assertJsonPath('message', __('market.not_listed'));
    }

    public function test_a_user_cannot_buy_a_player_they_cannot_afford(): void
    {
        [$buyer] = $this->userWithTeam(['budget' => 1_000_000]);
        [, $sellerTeam] = $this->userWithTeam();
        $player = Player::factory()->for($sellerTeam)->create();
        TransferList::factory()->for($player)->create(['asking_price' => 2_000_000]);

        Sanctum::actingAs($buyer);

        $this->postJson("/api/v1/market/{$player->id}/buy")
            ->assertUnprocessable()
            ->assertJsonPath('message', __('market.insufficient_funds'));

        $this->assertDatabaseHas('players', [
            'id' => $player->id,
            'team_id' => $sellerTeam->id,
        ]);
    }

    public function test_the_market_requires_authentication(): void
    {
        $this->getJson('/api/v1/market')->assertUnauthorized();
    }

    /**
     * @return array{0: User, 1: Team}
     */
    private function userWithTeam(array $teamAttributes = []): array
    {
        $user = User::factory()->create();
        $team = Team::factory()->for($user)->create($teamAttributes);

        return [$user, $team];
    }
}

<?php

namespace Tests\Unit\Actions;

use App\Actions\BuyPlayer;
use App\Models\Player;
use App\Models\Team;
use App\Models\TransferList;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class BuyPlayerTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_appreciates_the_player_value_between_ten_and_one_hundred_percent(): void
    {
        // Repeat to exercise the random appreciation factor across its range.
        for ($i = 0; $i < 30; $i++) {
            $player = $this->completePurchase(marketValue: 1_000_000, askingPrice: 2_000_000)->fresh();

            $this->assertGreaterThanOrEqual(1_100_000, $player->market_value);
            $this->assertLessThanOrEqual(2_000_000, $player->market_value);
        }
    }

    public function test_it_moves_the_asking_price_between_the_two_budgets(): void
    {
        $buyer = User::factory()->create();
        $buyerTeam = Team::factory()->for($buyer)->create(['budget' => 5_000_000]);
        $sellerTeam = Team::factory()->create(['budget' => 5_000_000]);
        $player = Player::factory()->for($sellerTeam)->create(['market_value' => 1_000_000]);
        TransferList::factory()->for($player)->create(['asking_price' => 2_000_000]);

        (new BuyPlayer())->handle($buyer, $player);

        $this->assertSame(3_000_000, $buyerTeam->fresh()->budget);
        $this->assertSame(7_000_000, $sellerTeam->fresh()->budget);
        $this->assertSame($buyerTeam->id, $player->fresh()->team_id);
    }

    public function test_it_rejects_buying_an_unlisted_player(): void
    {
        $buyer = User::factory()->create();
        Team::factory()->for($buyer)->create();
        $player = Player::factory()->create();

        $this->expectException(ValidationException::class);

        (new BuyPlayer())->handle($buyer, $player);
    }

    public function test_it_rejects_buying_your_own_player(): void
    {
        $buyer = User::factory()->create();
        $team = Team::factory()->for($buyer)->create();
        $player = Player::factory()->for($team)->create();
        TransferList::factory()->for($player)->create(['asking_price' => 1_000_000]);

        $this->expectException(ValidationException::class);

        (new BuyPlayer())->handle($buyer, $player);
    }

    public function test_it_rejects_a_purchase_the_buyer_cannot_afford(): void
    {
        $buyer = User::factory()->create();
        Team::factory()->for($buyer)->create(['budget' => 1_000_000]);
        $sellerTeam = Team::factory()->create();
        $player = Player::factory()->for($sellerTeam)->create();
        TransferList::factory()->for($player)->create(['asking_price' => 2_000_000]);

        $this->expectException(ValidationException::class);

        (new BuyPlayer())->handle($buyer, $player);
    }

    private function completePurchase(int $marketValue, int $askingPrice): Player
    {
        $buyer = User::factory()->create();
        Team::factory()->for($buyer)->create(['budget' => 10_000_000]);
        $sellerTeam = Team::factory()->create();
        $player = Player::factory()->for($sellerTeam)->create(['market_value' => $marketValue]);
        TransferList::factory()->for($player)->create(['asking_price' => $askingPrice]);

        return (new BuyPlayer())->handle($buyer, $player);
    }
}

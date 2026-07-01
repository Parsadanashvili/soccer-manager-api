<?php

namespace Tests\Feature\Api\V1;

use App\Models\Player;
use App\Models\Team;
use App\Models\TransferList;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class LocalizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_validation_messages_are_returned_in_english(): void
    {
        $this->postJson('/api/v1/register', [], ['Accept-Language' => 'en'])
            ->assertUnprocessable()
            ->assertJsonPath('errors.name.0', 'The name field is required.');
    }

    public function test_validation_messages_are_returned_in_georgian(): void
    {
        $this->postJson('/api/v1/register', [], ['Accept-Language' => 'ka'])
            ->assertUnprocessable()
            ->assertJsonPath('errors.name.0', 'სახელი ველის შევსება სავალდებულოა.');
    }

    public function test_the_default_locale_is_georgian(): void
    {
        // A client that expresses no language preference falls back to the
        // app's Georgian-first default. We send an empty Accept-Language
        // because Symfony's synthetic test request always injects a default
        // "en-us,en" header when the key is omitted entirely.
        $this->postJson('/api/v1/register', [], ['Accept-Language' => ''])
            ->assertUnprocessable()
            ->assertJsonPath('errors.name.0', 'სახელი ველის შევსება სავალდებულოა.');
    }

    public function test_domain_messages_respect_the_requested_locale(): void
    {
        [$user, $team] = $this->buyOwnPlayerScenario();

        Sanctum::actingAs($user);

        $this->postJson("/api/v1/market/{$team->players->first()->id}/buy", [], ['Accept-Language' => 'en'])
            ->assertJsonPath('errors.player.0', __('market.own_player', [], 'en'));

        $this->postJson("/api/v1/market/{$team->players->first()->id}/buy", [], ['Accept-Language' => 'ka'])
            ->assertJsonPath('errors.player.0', __('market.own_player', [], 'ka'));
    }

    /**
     * @return array{0: User, 1: Team}
     */
    private function buyOwnPlayerScenario(): array
    {
        $user = User::factory()->create();
        $team = Team::factory()->for($user)->create();
        $player = Player::factory()->for($team)->create();
        TransferList::factory()->for($player)->create(['asking_price' => 1_000_000]);

        return [$user, $team->load('players')];
    }
}

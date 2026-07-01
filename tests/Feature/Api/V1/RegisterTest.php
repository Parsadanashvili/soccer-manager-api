<?php

namespace Tests\Feature\Api\V1;

use App\Enums\Position;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegisterTest extends TestCase
{
    use RefreshDatabase;

    private array $payload = [
        'name' => 'Nika',
        'email' => 'nika@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ];

    public function test_registration_creates_a_user_with_a_token_and_team(): void
    {
        $response = $this->postJson('/api/v1/register', $this->payload);

        $response->assertCreated()
            ->assertJsonStructure([
                'token',
                'user' => ['id', 'name', 'email'],
                'team' => ['id', 'name', 'country', 'budget', 'value'],
            ])
            ->assertJsonPath('user.email', 'nika@example.com')
            ->assertJsonPath('team.budget', 5_000_000)
            ->assertJsonPath('team.value', 20_000_000);

        $this->assertDatabaseHas('users', ['email' => 'nika@example.com']);
    }

    public function test_registration_generates_a_squad_of_twenty_players(): void
    {
        $this->postJson('/api/v1/register', $this->payload)->assertCreated();

        $team = User::firstWhere('email', 'nika@example.com')->team;

        $this->assertCount(20, $team->players);
        $this->assertTrue($team->players->every(fn ($player) => $player->market_value === 1_000_000));
    }

    public function test_registration_generates_the_required_position_distribution(): void
    {
        $this->postJson('/api/v1/register', $this->payload)->assertCreated();

        $players = User::firstWhere('email', 'nika@example.com')->team->players;

        $this->assertSame(3, $players->where('position', Position::Goalkeeper)->count());
        $this->assertSame(6, $players->where('position', Position::Defender)->count());
        $this->assertSame(6, $players->where('position', Position::Midfielder)->count());
        $this->assertSame(5, $players->where('position', Position::Attacker)->count());
    }

    public function test_registration_requires_a_unique_email(): void
    {
        User::factory()->create(['email' => 'nika@example.com']);

        $this->postJson('/api/v1/register', $this->payload)
            ->assertUnprocessable()
            ->assertJsonValidationErrorFor('email');
    }

    public function test_registration_requires_a_confirmed_password(): void
    {
        $this->postJson('/api/v1/register', [
            ...$this->payload,
            'password_confirmation' => 'mismatch',
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrorFor('password');
    }
}

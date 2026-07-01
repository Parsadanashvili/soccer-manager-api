<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Team extends Model
{
    /** @use HasFactory<\Database\Factories\TeamFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'country',
        'budget',
    ];

    protected function casts(): array
    {
        return [
            'budget' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function players(): HasMany
    {
        return $this->hasMany(Player::class);
    }

    public function transfersIn(): HasMany
    {
        return $this->hasMany(Transfer::class, 'to_team_id');
    }

    public function transfersOut(): HasMany
    {
        return $this->hasMany(Transfer::class, 'from_team_id');
    }

    protected function value(): Attribute
    {
        return Attribute::make(
            get: fn(): int => (int) $this->players->sum('market_value'),
        )->shouldCache();
    }
}

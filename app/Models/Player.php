<?php

namespace App\Models;

use App\Enums\Position;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Player extends Model
{
    /** @use HasFactory<\Database\Factories\PlayerFactory> */
    use HasFactory;

    protected $fillable = [
        'team_id',
        'first_name',
        'last_name',
        'country',
        'age',
        'position',
        'market_value',
    ];

    protected function casts(): array
    {
        return [
            'age' => 'integer',
            'position' => Position::class,
            'market_value' => 'integer',
        ];
    }

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }


    public function transferListing(): HasOne
    {
        return $this->hasOne(TransferList::class);
    }

    public function transfers(): HasMany
    {
        return $this->hasMany(Transfer::class);
    }

    protected function fullName(): Attribute
    {
        return Attribute::make(
            get: fn(): string => trim("{$this->first_name} {$this->last_name}"),
        );
    }

    public function isOnTransferList(): bool
    {
        return $this->transferListing()->exists();
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'player_id',
    'asking_price',
])]
class TransferList extends Model
{
    /** @use HasFactory<\Database\Factories\TransferListFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'asking_price' => 'integer',
        ];
    }

    public function player(): BelongsTo
    {
        return $this->belongsTo(Player::class);
    }
}

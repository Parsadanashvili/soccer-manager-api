<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TransferList extends Model
{
    /** @use HasFactory<\Database\Factories\TransferListFactory> */
    use HasFactory;

    protected $fillable = [
        'player_id',
        'asking_price',
    ];

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

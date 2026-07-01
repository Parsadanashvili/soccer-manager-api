<?php

namespace App\Http\Resources\Api\V1;

use App\Models\Player;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Player
 */
class PlayerResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'full_name' => $this->full_name,
            'country' => $this->country,
            'age' => $this->age,
            'position' => $this->position->value,
            'market_value' => $this->market_value,
        ];
    }
}

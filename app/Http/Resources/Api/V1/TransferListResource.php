<?php

namespace App\Http\Resources\Api\V1;

use App\Models\TransferList;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin TransferList
 */
class TransferListResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'asking_price' => $this->asking_price,
            'player' => PlayerResource::make($this->whenLoaded('player')),
        ];
    }
}

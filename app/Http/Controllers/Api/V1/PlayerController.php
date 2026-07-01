<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Player\UpdatePlayerRequest;
use App\Http\Resources\Api\V1\PlayerResource;
use App\Models\Player;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class PlayerController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $team = $request->user()->team()->firstOrFail();

        return PlayerResource::collection($team->players()->get());
    }

    /**
     * Update the specified resource.
     */
    public function update(UpdatePlayerRequest $request, Player $player): PlayerResource
    {
        $player->update($request->validated());

        return PlayerResource::make($player);
    }
}

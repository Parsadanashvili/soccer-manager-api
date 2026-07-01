<?php

namespace App\Http\Controllers\Api\V1;

use App\Actions\BuyPlayer;
use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\PlayerResource;
use App\Http\Resources\Api\V1\TransferListResource;
use App\Models\Player;
use App\Models\TransferList;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class MarketController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): AnonymousResourceCollection
    {
        $listings = TransferList::query()
            ->with('player.team')
            ->latest()
            ->get();

        return TransferListResource::collection($listings);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function buy(Request $request, Player $player, BuyPlayer $buyPlayer): PlayerResource
    {
        $player = $buyPlayer->handle($request->user(), $player);

        return PlayerResource::make($player->load('team'));
    }
}

<?php

namespace App\Http\Controllers\Api\V1\Player;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Player\StoreTransferListRequest;
use App\Http\Resources\Api\V1\TransferListResource;
use App\Models\Player;
use App\Models\TransferList;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Gate;

class TransferListController extends Controller
{
    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreTransferListRequest $request, Player $player): JsonResponse
    {
        $listing = $player->transferListing()->create([
            'asking_price' => $request->validated('asking_price'),
        ]);

        return TransferListResource::make($listing->load('player'))
            ->response()
            ->setStatusCode(201);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(TransferList $transferList): Response
    {
        Gate::authorize('delete', $transferList);

        $transferList->delete();

        return response()->noContent();
    }
}

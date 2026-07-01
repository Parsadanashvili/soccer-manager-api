<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Team\UpdateTeamRequest;
use App\Http\Resources\Api\V1\TeamResource;
use Illuminate\Http\Request;

class TeamController extends Controller
{
    /**
     * Display the resource.
     */
    public function show(Request $request): TeamResource
    {
        $team = $request->user()->team()->with('players')->firstOrFail();

        return TeamResource::make($team);
    }

    /**
     * Update the resource.
     */
    public function update(UpdateTeamRequest $request): TeamResource
    {
        $team = $request->user()->team()->firstOrFail();
        $team->update($request->validated());

        return TeamResource::make($team->load('players'));
    }
}

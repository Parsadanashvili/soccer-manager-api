<?php

use App\Http\Controllers\Api\V1 as V1;
use Illuminate\Support\Facades\Route;

Route::prefix("v1")->name('api.v1.')->group(function () {
    Route::post('/register', [V1\AuthController::class, "register"])->name('register');
    Route::post('/login',    [V1\AuthController::class, "login"])->name('login');

    Route::middleware("auth:sanctum")->group(function () {
        Route::post('/logout', [V1\AuthController::class, "logout"])->name("logout");

        Route::apiSingleton('/team', V1\TeamController::class);

        Route::apiResource('players', V1\PlayerController::class)
            ->only(['index', 'update']);

        Route::apiResource('players.transfer-list', V1\Player\TransferListController::class)
            ->only(['store', 'destroy'])
            ->shallow();

        Route::get('/market', [V1\MarketController::class, 'index'])->name('market.index');
        Route::post('/market/{player}/buy', [V1\MarketController::class, 'buy'])->name('market.buy');
    });
});

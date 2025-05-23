<?php

use App\Http\Controllers\LeaderboardController;
use Illuminate\Support\Facades\Route;

Route::get('/', [LeaderboardController::class, 'index'])->name('leaderboard.index');
Route::post('/leaderboard/recalculate', [LeaderboardController::class, 'recalculate'])->name('leaderboard.recalculate');

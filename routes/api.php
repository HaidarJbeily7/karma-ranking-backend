<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UsersController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/


Route::get('v3/user/{id}/karma-position', [UsersController::class, 'getUsersForLeaderboard3']);
Route::get('v2/user/{id}/karma-position', [UsersController::class, 'getUsersForLeaderboard2']);
Route::get('v1/user/{id}/karma-position', [UsersController::class, 'getUsersForLeaderboard']);

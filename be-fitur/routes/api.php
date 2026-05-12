<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\MenuController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\BrowseController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

Route::prefix('auth')->group(function () {
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);
});

Route::prefix('menu')->group(function () {
    Route::get('/', [MenuController::class, 'index']);
});

Route::prefix('menus')->group(function () {
    Route::get('/sidebar', [MenuController::class, 'sidebar']);
});

Route::prefix('browse')->group(function () {
    Route::get('/types', [BrowseController::class, 'types']);
    Route::get('/{kodeBrowse}', [BrowseController::class, 'search']);
    Route::get('/{kodeBrowse}/config', [BrowseController::class, 'config']);
});

Route::prefix('reports')->group(function () {
    Route::get('/menu', [ReportController::class, 'menu']);
    Route::get('/perkiraan/search', [ReportController::class, 'searchPerkiraan']);
    Route::get('/{kodeMenu}', [ReportController::class, 'show']);
    Route::post('/{kodeMenu}/preview', [ReportController::class, 'preview']);
});
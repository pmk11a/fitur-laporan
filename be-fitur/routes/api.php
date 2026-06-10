<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\MenuController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\BrowseController;
use App\Http\Controllers\AdminReportController;
use App\Http\Controllers\Api\UserPreferenceController;

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

// User preferences — universal key-value store
Route::prefix('preferences')->group(function () {
    Route::get('/', [UserPreferenceController::class, 'index']);
    Route::post('/bulk', [UserPreferenceController::class, 'bulkUpdate']);
    Route::get('/{namespace}/{key}', [UserPreferenceController::class, 'show']);
    Route::put('/{namespace}/{key}', [UserPreferenceController::class, 'update']);
    Route::delete('/{namespace}/{key}', [UserPreferenceController::class, 'destroy']);
});

// Admin routes — auth (userId dari query/body) + admin role check
Route::prefix('admin')->middleware(['auth', 'admin'])->group(function () {
    // Reports CRUD
    Route::get('/reports', [AdminReportController::class, 'index']);
    Route::post('/reports', [AdminReportController::class, 'store']);
    Route::get('/reports/available-kodemenu', [AdminReportController::class, 'availableKodeMenu']);
    Route::get('/reports/{id}', [AdminReportController::class, 'show']);
    Route::put('/reports/{id}', [AdminReportController::class, 'update']);
    Route::delete('/reports/{id}', [AdminReportController::class, 'destroy']);

    // Filters
    Route::get('/reports/{id}/filters', [AdminReportController::class, 'listFilters']);
    Route::post('/reports/{id}/filters', [AdminReportController::class, 'storeFilter']);
    Route::put('/reports/{id}/filters/{fid}', [AdminReportController::class, 'updateFilter']);
    Route::delete('/reports/{id}/filters/{fid}', [AdminReportController::class, 'destroyFilter']);
    Route::patch('/reports/{id}/filters/reorder', [AdminReportController::class, 'reorderFilters']);

    // Datasets
    Route::get('/reports/{id}/datasets', [AdminReportController::class, 'listDatasets']);
    Route::post('/reports/{id}/datasets', [AdminReportController::class, 'storeDataset']);
    Route::put('/reports/{id}/datasets/{did}', [AdminReportController::class, 'updateDataset']);
    Route::delete('/reports/{id}/datasets/{did}', [AdminReportController::class, 'destroyDataset']);
    Route::post('/reports/{id}/datasets/preview', [AdminReportController::class, 'previewDataset']);

    // Columns
    Route::get('/reports/{id}/columns', [AdminReportController::class, 'listColumns']);
    Route::post('/reports/{id}/columns', [AdminReportController::class, 'storeColumn']);
    Route::put('/reports/{id}/columns/{cid}', [AdminReportController::class, 'updateColumn']);
    Route::delete('/reports/{id}/columns/{cid}', [AdminReportController::class, 'destroyColumn']);

    // Groups
    Route::get('/reports/{id}/groups', [AdminReportController::class, 'listGroups']);
    Route::post('/reports/{id}/groups', [AdminReportController::class, 'storeGroup']);
    Route::put('/reports/{id}/groups/{gid}', [AdminReportController::class, 'updateGroup']);
    Route::delete('/reports/{id}/groups/{gid}', [AdminReportController::class, 'destroyGroup']);

    // Menu items
    Route::get('/menu-items', [AdminReportController::class, 'listMenuItems']);
    Route::put('/menu-items/{kodeMenu}', [AdminReportController::class, 'updateMenuItem']);

    // User access
    Route::get('/reports/{id}/access', [AdminReportController::class, 'listAccess']);
    Route::post('/reports/{id}/access', [AdminReportController::class, 'grantAccess']);
    Route::delete('/reports/{id}/access/{userId}', [AdminReportController::class, 'revokeAccess']);
    Route::get('/users', [AdminReportController::class, 'listUsers']);
});
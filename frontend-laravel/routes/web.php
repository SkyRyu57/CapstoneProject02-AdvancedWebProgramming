<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Dashboard\DashboardController;
use App\Http\Controllers\Admin\RoomController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Kaprodi\ProcurementReviewController;
use App\Http\Controllers\KepalaLab\ProcurementDraftController;
use App\Http\Controllers\StafAdmin\ApprovedDraftController;
use App\Http\Controllers\StafAdmin\InventoryController;
use App\Http\Controllers\StafLab\ConsumableController;
use App\Http\Controllers\StafLab\MaintenanceController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('login');
});

Route::middleware('guest.session')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.store');
});

Route::get('/forgot-account', [AuthController::class, 'showForgotAccount'])->name('forgot-account');
Route::post('/forgot-account', [AuthController::class, 'forgotAccount'])->name('forgot-account.store');

Route::get('/dashboard', DashboardController::class)->name('dashboard');

Route::prefix('admin')->name('admin.')->group(function () {
    Route::get('/users', [UserController::class, 'index'])->name('users.index');
    Route::post('/users', [UserController::class, 'store'])->name('users.store');
    Route::patch('/users/{id}', [UserController::class, 'update'])->name('users.update');
    Route::delete('/users/{id}', [UserController::class, 'destroy'])->name('users.destroy');
    Route::get('/rooms', [RoomController::class, 'index'])->name('rooms.index');
    Route::post('/rooms', [RoomController::class, 'store'])->name('rooms.store');
    Route::patch('/rooms/{id}', [RoomController::class, 'update'])->name('rooms.update');
    Route::delete('/rooms/{id}', [RoomController::class, 'destroy'])->name('rooms.destroy');
});

Route::prefix('kaprodi')->name('kaprodi.')->group(function () {
    Route::get('/procurement-drafts', [ProcurementReviewController::class, 'index'])->name('drafts.index');
    Route::get('/procurement-drafts/{id}', [ProcurementReviewController::class, 'show'])->name('drafts.show');
    Route::patch('/procurement-drafts/{draftId}/items/{itemId}/review', [ProcurementReviewController::class, 'reviewItem'])->name('drafts.items.review');
    Route::patch('/procurement-drafts/{draftId}/finalize', [ProcurementReviewController::class, 'finalize'])->name('drafts.finalize');
});

Route::prefix('staf-admin')->name('staf-admin.')->group(function () {
    Route::get('/approved-drafts', [ApprovedDraftController::class, 'index'])->name('approved-drafts.index');
    Route::post('/receipts', [ApprovedDraftController::class, 'storeReceipt'])->name('receipts.store');
    Route::get('/inventories', [InventoryController::class, 'index'])->name('inventories.index');
    Route::patch('/inventories/{id}', [InventoryController::class, 'update'])->name('inventories.update');
    Route::delete('/inventories/{id}', [InventoryController::class, 'destroy'])->name('inventories.destroy');
});

Route::prefix('kepala-lab')->name('kepala-lab.')->group(function () {
    Route::get('/procurement-drafts', [ProcurementDraftController::class, 'index'])->name('drafts.index');
    Route::post('/procurement-drafts', [ProcurementDraftController::class, 'store'])->name('drafts.store');
    Route::get('/procurement-drafts/{id}', [ProcurementDraftController::class, 'show'])->name('drafts.show');
    Route::patch('/procurement-drafts/{id}', [ProcurementDraftController::class, 'update'])->name('drafts.update');
    Route::delete('/procurement-drafts/{id}', [ProcurementDraftController::class, 'destroy'])->name('drafts.destroy');
    Route::post('/procurement-drafts/{id}/items', [ProcurementDraftController::class, 'storeItem'])->name('drafts.items.store');
    Route::patch('/procurement-drafts/{id}/items/{itemId}', [ProcurementDraftController::class, 'updateItem'])->name('drafts.items.update');
    Route::delete('/procurement-drafts/{id}/items/{itemId}', [ProcurementDraftController::class, 'destroyItem'])->name('drafts.items.destroy');
});

Route::prefix('staf-lab')->name('staf-lab.')->group(function () {
    Route::get('/consumables', [ConsumableController::class, 'index'])->name('consumables.index');
    Route::post('/consumables/{id}/adjust', [ConsumableController::class, 'adjust'])->name('consumables.adjust');
    Route::get('/maintenance', [MaintenanceController::class, 'index'])->name('maintenance.index');
    Route::post('/maintenance', [MaintenanceController::class, 'store'])->name('maintenance.store');
});

Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\DepartmentController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\AssetLoanController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ItemRequestController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\EmployeeTransferController;

Route::get('/item-requests', [ItemRequestController::class, 'index'])->name('item-requests.index');
Route::post('/item-requests', [ItemRequestController::class, 'store'])->name('item-requests.store');
Route::delete('/item-requests/{itemRequest}', [ItemRequestController::class, 'destroy'])->name('item-requests.destroy');

Route::middleware('guest')->group(function () {
    Route::get('/', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('/', [AuthenticatedSessionController::class, 'store']);
});

Route::middleware(['auth'])->group(function () {
    Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Asset Loans
    Route::get('/asset-loans', [AssetLoanController::class, 'index'])->name('asset-loans.index');
    Route::post('/asset-loans', [AssetLoanController::class, 'store'])->name('asset-loans.store');
    Route::put('/asset-loans/{assetLoan}', [AssetLoanController::class, 'update'])->name('asset-loans.update');
    Route::delete('/asset-loans/{assetLoan}', [AssetLoanController::class, 'destroy'])->name('asset-loans.destroy');
    Route::post('/asset-loans/{assetLoan}/approve', [AssetLoanController::class, 'approve'])->name('asset-loans.approve');
    Route::post('/asset-loans/{assetLoan}/kembalikan', [AssetLoanController::class, 'kembalikan'])->name('asset-loans.kembalikan');

    // Reports
    Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
    Route::get('/reports/export', [ReportController::class, 'export'])->name('reports.export');

    // Inventory
    Route::get('/inventory', [InventoryController::class, 'index'])->name('inventory.index');

    // Tipe Asset Besar
    Route::post('/inventory/asset-types', [InventoryController::class, 'storeAssetType'])->name('asset-types.store');
    Route::put('/inventory/asset-types/{assetType}', [InventoryController::class, 'updateAssetType'])->name('asset-types.update');
    Route::delete('/inventory/asset-types/{assetType}', [InventoryController::class, 'destroyAssetType'])->name('asset-types.destroy');

    // Tipe Asset Kecil
    Route::post('/inventory/small-asset-types', [InventoryController::class, 'storeSmallAssetType'])->name('small-asset-types.store');
    Route::put('/inventory/small-asset-types/{smallAssetType}', [InventoryController::class, 'updateSmallAssetType'])->name('small-asset-types.update');
    Route::delete('/inventory/small-asset-types/{smallAssetType}', [InventoryController::class, 'destroySmallAssetType'])->name('small-asset-types.destroy');

    // Asset Besar
    Route::post('/inventory/large', [InventoryController::class, 'storeLargeAsset'])->name('large-assets.store');
    Route::put('/inventory/large/{largeAsset}', [InventoryController::class, 'updateLargeAsset'])->name('large-assets.update');
    Route::delete('/inventory/large/{largeAsset}', [InventoryController::class, 'destroyLargeAsset'])->name('large-assets.destroy');
    Route::get('/inventory/export-large', [InventoryController::class, 'exportLarge'])->name('inventory.export-large');

    // Asset Kecil
    Route::post('/inventory/small', [InventoryController::class, 'storeSmallAsset'])->name('small-assets.store');
    Route::put('/inventory/small/{smallAsset}', [InventoryController::class, 'updateSmallAsset'])->name('small-assets.update');
    Route::delete('/inventory/small/{smallAsset}', [InventoryController::class, 'destroySmallAsset'])->name('small-assets.destroy');
    Route::post('/inventory/small/{smallAsset}/restock', [InventoryController::class, 'restock'])->name('small-assets.restock');
    Route::get('/inventory/export-small', [InventoryController::class, 'exportSmall'])->name('inventory.export-small');

    // Item Requests
    Route::get('/item-requests', [ItemRequestController::class, 'index'])->name('item-requests.index');
    Route::post('/item-requests', [ItemRequestController::class, 'store'])->name('item-requests.store');
    Route::delete('/item-requests/{itemRequest}', [ItemRequestController::class, 'destroy'])->name('item-requests.destroy');
    Route::get('/item-requests/export', [ItemRequestController::class, 'export'])->name('item-requests.export');

    // Employees
    Route::resource('employees', EmployeeController::class)->except(['create', 'edit', 'show']);

    // Users
    Route::resource('users', UserController::class)->except(['create', 'edit', 'show']);

    //Department
    Route::get('/departments', [DepartmentController::class, 'index'])->name('departments.index');

    // Divisions
    Route::post('/divisions', [DepartmentController::class, 'storeDivision'])->name('divisions.store');
    Route::put('/divisions/{division}', [DepartmentController::class, 'updateDivision'])->name('divisions.update');
    Route::delete('/divisions/{division}', [DepartmentController::class, 'destroyDivision'])->name('divisions.destroy');

    // Positions
    Route::post('/positions', [DepartmentController::class, 'storePosition'])->name('positions.store');
    Route::put('/positions/{position}', [DepartmentController::class, 'updatePosition'])->name('positions.update');
    Route::delete('/positions/{position}', [DepartmentController::class, 'destroyPosition'])->name('positions.destroy');

    // Employee Transfer
    Route::get('/employee-transfers', [EmployeeTransferController::class, 'index'])->name('employee-transfers.index');
    Route::post('/employee-transfers', [EmployeeTransferController::class, 'store'])->name('employee-transfers.store');
    Route::delete('/employee-transfers/{employeeTransfer}', [EmployeeTransferController::class, 'destroy'])->name('employee-transfers.destroy');
});
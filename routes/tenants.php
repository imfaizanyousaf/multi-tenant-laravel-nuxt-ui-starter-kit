<?php

declare(strict_types=1);

use App\Http\Controllers\TenantController;
use App\Http\Middleware\LandlordOnly;
use Illuminate\Support\Facades\Route;

Route::middleware([LandlordOnly::class])->group(function (): void {
    Route::get('tenants', [TenantController::class, 'index'])->name('tenants');
    Route::get('tenants/table', [TenantController::class, 'table'])->name('tenants.table');
    Route::post('tenants', [TenantController::class, 'store'])->name('tenants.store');
    Route::delete('tenants/{tenant}', [TenantController::class, 'destroy'])->name('tenants.destroy');
});

<?php

declare(strict_types=1);

use App\Http\Controllers\RoleController;
use Illuminate\Support\Facades\Route;

Route::get('roles', [RoleController::class, 'index'])->name('roles');
Route::get('roles/table', [RoleController::class, 'table'])->name('roles.table');
Route::get('roles/options', [RoleController::class, 'options'])->name('roles.options');
Route::post('roles', [RoleController::class, 'store'])->name('roles.store');
Route::put('roles/{role}', [RoleController::class, 'update'])->name('roles.update');
Route::delete('roles/bulk', [RoleController::class, 'destroyBulk'])->name('roles.destroy-bulk');
Route::delete('roles/{role}', [RoleController::class, 'destroy'])->name('roles.destroy');

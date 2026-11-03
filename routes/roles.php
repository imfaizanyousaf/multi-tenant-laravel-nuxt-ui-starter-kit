<?php

declare(strict_types=1);

use App\Http\Controllers\RoleBulkDeletionController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\RoleOptionsController;
use App\Http\Controllers\RoleTableController;
use Illuminate\Support\Facades\Route;

Route::get('roles', [RoleController::class, 'index'])->name('roles');
Route::get('roles/table', RoleTableController::class)->name('roles.table');
Route::get('roles/options', RoleOptionsController::class)->name('roles.options');
Route::post('roles', [RoleController::class, 'store'])->name('roles.store');
Route::put('roles/{role}', [RoleController::class, 'update'])->name('roles.update');
Route::delete('roles/bulk', [RoleBulkDeletionController::class, 'destroy'])->name('roles.destroy-bulk');
Route::delete('roles/{role}', [RoleController::class, 'destroy'])->name('roles.destroy');

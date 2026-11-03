<?php

declare(strict_types=1);

use App\Http\Controllers\UserBulkDeletionController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\UserOptionsController;
use App\Http\Controllers\UserTableController;
use Illuminate\Support\Facades\Route;

Route::get('users', [UserController::class, 'index'])->name('users');
Route::get('users/table', UserTableController::class)->name('users.table');
Route::get('users/options', UserOptionsController::class)->name('users.options');
Route::post('users', [UserController::class, 'store'])->name('users.store');
Route::put('users/{user}', [UserController::class, 'update'])->name('users.update');
Route::delete('users/bulk', [UserBulkDeletionController::class, 'destroy'])->name('users.destroy-bulk');
Route::delete('users/{user}', [UserController::class, 'destroy'])->name('users.destroy');

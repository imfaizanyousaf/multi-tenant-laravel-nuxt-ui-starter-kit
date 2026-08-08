<?php

declare(strict_types=1);

use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::get('users', [UserController::class, 'index'])->name('users');
Route::get('users/table', [UserController::class, 'table'])->name('users.table');
Route::get('users/options', [UserController::class, 'options'])->name('users.options');
Route::post('users', [UserController::class, 'store'])->name('users.store');
Route::put('users/{user}', [UserController::class, 'update'])->name('users.update');
Route::delete('users/bulk', [UserController::class, 'destroyBulk'])->name('users.destroy-bulk');
Route::delete('users/{user}', [UserController::class, 'destroy'])->name('users.destroy');

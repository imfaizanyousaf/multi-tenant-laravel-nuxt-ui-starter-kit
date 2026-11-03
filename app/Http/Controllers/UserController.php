<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\User\CreateUser;
use App\Actions\User\DeleteUser;
use App\Actions\User\UpdateUser;
use App\Http\Requests\User\CreateUserRequest;
use App\Http\Requests\User\DeleteUserRequest;
use App\Http\Requests\User\ListUserRequest;
use App\Http\Requests\User\UpdateUserRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

use function back;

final class UserController
{
    /**
     * Display a listing of users.
     */
    public function index(ListUserRequest $request): Response
    {
        return Inertia::render('Users');
    }

    /**
     * Store a newly created user in storage.
     */
    public function store(CreateUserRequest $request, CreateUser $createUser): RedirectResponse
    {
        $createUser->handle($request->validated());

        return back();
    }

    /**
     * Update the specified user in storage.
     */
    public function update(UpdateUserRequest $request, User $user, UpdateUser $updateUser): RedirectResponse
    {
        $updateUser->handle($user, $request->validated());

        return back();
    }

    /**
     * Remove the specified user from storage.
     */
    public function destroy(DeleteUserRequest $request, User $user, DeleteUser $deleteUser): RedirectResponse
    {
        $deleteUser->handle($user);

        return back();
    }
}

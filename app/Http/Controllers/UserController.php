<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\User\CreateUser;
use App\Actions\User\DeleteBulkUsers;
use App\Actions\User\DeleteUser;
use App\Actions\User\GetPaginatedUsers;
use App\Actions\User\UpdateUser;
use App\Http\Requests\User\DestroyBulkUserRequest;
use App\Http\Requests\User\StoreUserRequest;
use App\Http\Requests\User\UpdateUserRequest;
use App\Http\Resources\DatatableResourceCollection;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

use function auth;
use function back;

class UserController extends Controller
{
    /**
     * Display a listing of real users from DB.
     */
    public function index(): Response
    {
        return Inertia::render('Users', [
            'users' => UserResource::collection(User::query()->latest()->get())->resolve(),
        ]);
    }

    /**
     * Get paginated users for server-side datatable.
     */
    public function table(Request $request, GetPaginatedUsers $getPaginatedUsers): DatatableResourceCollection
    {
        $users = $getPaginatedUsers->handle($request);

        return new DatatableResourceCollection($users, UserResource::class);
    }

    /**
     * Store a newly created user in storage.
     */
    public function store(StoreUserRequest $request, CreateUser $createUser): RedirectResponse
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
    public function destroy(User $user, DeleteUser $deleteUser): RedirectResponse
    {
        if ($user->id === auth()->id()) {
            return back()->withErrors(['user' => 'You cannot delete your own account.']);
        }

        $deleteUser->handle($user);

        return back();
    }

    /**
     * Remove multiple specified users from storage.
     */
    public function destroyBulk(DestroyBulkUserRequest $request, DeleteBulkUsers $deleteBulkUsers): RedirectResponse
    {
        /** @var array<int, string> $ids */
        $ids = $request->validated('ids');

        $deleteBulkUsers->handle($ids, (string) auth()->id());

        return back();
    }
}

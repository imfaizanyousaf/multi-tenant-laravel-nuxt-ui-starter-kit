<?php

declare(strict_types=1);

namespace App\Http\Controllers\Settings;

use App\Actions\User\DeleteUser;
use App\Actions\User\UpdateUser;
use App\Http\Requests\Settings\DeleteProfileRequest;
use App\Http\Requests\Settings\UpdateProfileRequest;
use App\Models\User;
use Illuminate\Container\Attributes\CurrentUser;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

use function redirect;
use function to_route;

final class ProfileController
{
    /**
     * Show the user's profile settings page.
     */
    public function edit(Request $request): Response
    {
        return Inertia::render('settings/Profile', [
            'mustVerifyEmail' => $request->user() instanceof MustVerifyEmail,
            'status' => $request->session()->get('status'),
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(
        UpdateProfileRequest $request,
        #[CurrentUser] User $user,
        UpdateUser $updateUser,
    ): RedirectResponse {
        $updateUser->handle($user, $request->validated());

        return to_route('profile.edit');
    }

    /**
     * Delete the user's profile.
     */
    public function destroy(
        DeleteProfileRequest $request,
        #[CurrentUser] User $user,
        DeleteUser $deleteUser,
    ): RedirectResponse {
        $deleteUser->handle($user);

        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}

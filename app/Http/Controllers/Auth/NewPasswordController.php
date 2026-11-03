<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Actions\Auth\UpdateUserPasswordWithToken;
use App\Http\Requests\Auth\CreateNewPasswordRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Inertia\Inertia;
use Inertia\Response;

use function __;
use function back;
use function to_route;

final class NewPasswordController
{
    /**
     * Show the password reset page.
     */
    public function create(Request $request): Response
    {
        return Inertia::render('auth/ResetPassword', [
            'email' => $request->email,
            'token' => $request->route('token'),
        ]);
    }

    /**
     * Handle an incoming new password request.
     */
    public function store(CreateNewPasswordRequest $request, UpdateUserPasswordWithToken $updateUserPasswordWithToken): RedirectResponse
    {
        $status = $updateUserPasswordWithToken->handle($request->credentials());

        if ($status === Password::PasswordReset) {
            return to_route('login')->with('status', __($status));
        }

        return back()->withErrors([
            'email' => __($status),
        ]);
    }
}

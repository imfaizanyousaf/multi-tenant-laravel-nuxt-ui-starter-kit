<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Actions\Auth\CreatePasswordResetLink;
use App\Http\Requests\Auth\CreatePasswordResetLinkRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

use function __;
use function back;

final class PasswordResetLinkController
{
    /**
     * Show the password reset link request page.
     */
    public function create(Request $request): Response
    {
        return Inertia::render('auth/ForgotPassword', [
            'status' => $request->session()->get('status'),
        ]);
    }

    /**
     * Handle an incoming password reset link request.
     */
    public function store(CreatePasswordResetLinkRequest $request, CreatePasswordResetLink $createPasswordResetLink): RedirectResponse
    {
        $createPasswordResetLink->handle($request->email());

        return back()->with('status', __('A reset link will be sent if the account exists.'));
    }
}

<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Actions\Auth\CreateRegistration;
use App\Http\Requests\Auth\CreateRegistrationRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

use function to_route;

final class RegisteredUserController
{
    /**
     * Show the registration page.
     */
    public function create(): Response
    {
        return Inertia::render('auth/Register');
    }

    /**
     * Handle an incoming registration request.
     */
    public function store(CreateRegistrationRequest $request, CreateRegistration $createRegistration): RedirectResponse
    {
        $user = $createRegistration->handle($request->validated());

        Auth::login($user);

        return to_route('dashboard');
    }
}

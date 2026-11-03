<?php

declare(strict_types=1);

namespace App\Actions\Auth;

use Illuminate\Support\Facades\Password;

final readonly class CreatePasswordResetLink
{
    /**
     * Send a password reset link if the account exists.
     */
    public function handle(string $email): void
    {
        Password::sendResetLink(['email' => $email]);
    }
}

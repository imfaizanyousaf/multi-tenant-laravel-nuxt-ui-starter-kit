<?php

declare(strict_types=1);

namespace App\Actions\Auth;

use App\Actions\User\UpdateUserPassword;
use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;

use function event;

final readonly class UpdateUserPasswordWithToken
{
    public function __construct(private UpdateUserPassword $updateUserPassword)
    {
        //
    }

    /**
     * Reset the user's password using a valid reset token.
     *
     * @param  array{email: string, password: string, token: string}  $credentials
     */
    public function handle(array $credentials): string
    {
        return Password::reset(
            $credentials,
            function (User $user) use ($credentials): void {
                $this->updateUserPassword->handle($user, $credentials['password'], Str::random(60));

                event(new PasswordReset($user));
            },
        );
    }
}

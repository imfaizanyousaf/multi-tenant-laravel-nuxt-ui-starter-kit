<?php

declare(strict_types=1);

namespace App\Http\Controllers\Settings;

use App\Actions\User\UpdateUserPassword;
use App\Http\Requests\Settings\UpdatePasswordRequest;
use App\Models\User;
use Illuminate\Container\Attributes\CurrentUser;
use Illuminate\Http\RedirectResponse;

use function back;

final class PasswordController
{
    /**
     * Update the user's password.
     */
    public function update(
        UpdatePasswordRequest $request,
        #[CurrentUser] User $user,
        UpdateUserPassword $updateUserPassword,
    ): RedirectResponse {
        $updateUserPassword->handle($user, (string) $request->validated('password'));

        return back();
    }
}

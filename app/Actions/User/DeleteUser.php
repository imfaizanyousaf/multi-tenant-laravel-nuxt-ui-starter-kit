<?php

declare(strict_types=1);

namespace App\Actions\User;

use App\Models\User;

class DeleteUser
{
    /**
     * Delete a single user record.
     */
    public function handle(User $user): bool
    {
        return (bool) $user->delete();
    }
}

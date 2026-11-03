<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\User\DeleteBulkUsers;
use App\Http\Requests\User\DeleteBulkUserRequest;
use App\Models\User;
use Illuminate\Container\Attributes\CurrentUser;
use Illuminate\Http\RedirectResponse;

use function back;

final class UserBulkDeletionController
{
    /**
     * Remove multiple specified users from storage.
     */
    public function destroy(
        DeleteBulkUserRequest $request,
        #[CurrentUser] User $currentUser,
        DeleteBulkUsers $deleteBulkUsers,
    ): RedirectResponse {
        /** @var array<int, string> $ids */
        $ids = $request->validated('ids');

        $deleteBulkUsers->handle($ids, (string) $currentUser->id, true);

        return back();
    }
}

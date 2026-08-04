<?php

declare(strict_types=1);

namespace App\Actions\User;

use App\Models\User;

class DeleteBulkUsers
{
    /**
     * Delete multiple user records excluding the current authenticated user.
     *
     * @param  array<int, string>  $ids
     */
    public function handle(array $ids, string $currentUserId): int
    {
        return User::query()
            ->where(function ($query) use ($ids): void {
                $query->whereIn('uuid', $ids)
                    ->orWhereIn('id', $ids);
            })
            ->where('id', '!=', $currentUserId)
            ->delete();
    }
}

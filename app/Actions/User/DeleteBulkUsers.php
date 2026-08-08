<?php

declare(strict_types=1);

namespace App\Actions\User;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

use function array_filter;
use function is_numeric;
use function is_string;
use function str_contains;

class DeleteBulkUsers
{
    /**
     * Delete multiple user records excluding the current authenticated user.
     *
     * @param  array<int, mixed>  $ids
     */
    public function handle(array $ids, string $currentUserId, bool $excludeSuperAdmins = false): int
    {
        $uuids = array_filter($ids, static fn (mixed $id): bool => is_string($id) && str_contains($id, '-'));
        $numericIds = array_filter($ids, is_numeric(...));

        if ($uuids === [] && $numericIds === []) {
            return 0;
        }

        $query = User::query()
            ->where(static function (Builder $query) use ($uuids, $numericIds): void {
                if ($uuids !== []) {
                    $query->whereIn('uuid', $uuids);
                }
                if ($numericIds !== []) {
                    $query->orWhereIn('id', $numericIds);
                }
            })
            ->where('id', '!=', $currentUserId);

        if ($excludeSuperAdmins) {
            $query->whereDoesntHave('roles', static fn (Builder $query): Builder => $query->where('name', Role::SUPER_ADMIN));
        }

        return $query->delete();
    }
}

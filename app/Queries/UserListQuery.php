<?php

declare(strict_types=1);

namespace App\Queries;

use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

use function explode;
use function in_array;
use function str_contains;
use function strtolower;

final readonly class UserListQuery
{
    /**
     * Get paginated users with filtering, sorting, and search.
     */
    public function handle(
        string $search = '',
        string $sort = '',
        int $perPage = 10,
        ?string $emailVerified = null,
    ): LengthAwarePaginator {
        $query = User::query()->with('roles.permissions');

        if ($search !== '') {
            $query->where(static function (Builder $query) use ($search): void {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($emailVerified === 'verified') {
            $query->whereNotNull('email_verified_at');
        } elseif ($emailVerified === 'unverified') {
            $query->whereNull('email_verified_at');
        }

        $ordered = false;

        if ($sort !== '') {
            foreach (explode('-', $sort) as $part) {
                if (! str_contains($part, '.')) {
                    continue;
                }

                [$column, $direction] = explode('.', $part, 2);
                $direction = strtolower($direction) === 'desc' ? 'desc' : 'asc';

                if (in_array($column, ['id', 'uuid', 'name', 'email', 'created_at', 'email_verified_at'], true)) {
                    $query->orderBy($column, $direction);
                    $ordered = true;
                }
            }
        }

        if (! $ordered) {
            $query->latest();
        }

        return $query->paginate($perPage);
    }
}

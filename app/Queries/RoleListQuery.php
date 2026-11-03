<?php

declare(strict_types=1);

namespace App\Queries;

use App\Models\Role;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

use function explode;
use function in_array;
use function str_contains;
use function strtolower;

final readonly class RoleListQuery
{
    /**
     * Get paginated roles with filtering, sorting, and search.
     */
    public function handle(string $search = '', string $sort = '', int $perPage = 10): LengthAwarePaginator
    {
        $query = Role::query()->with('permissions');

        if ($search !== '') {
            $query->where(static function (Builder $query) use ($search): void {
                $query->where('name', 'like', "%{$search}%");
            });
        }

        $ordered = false;

        if ($sort !== '') {
            foreach (explode('-', $sort) as $part) {
                if (! str_contains($part, '.')) {
                    continue;
                }

                [$column, $direction] = explode('.', $part, 2);
                $direction = strtolower($direction) === 'desc' ? 'desc' : 'asc';

                if (in_array($column, ['id', 'name', 'created_at', 'updated_at'], true)) {
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

<?php

declare(strict_types=1);

namespace App\Actions\Role;

use App\Models\Role;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

use function explode;
use function in_array;
use function is_string;
use function request;
use function str_contains;
use function strtolower;

class GetPaginatedRoles
{
    /**
     * Get paginated roles with filtering, sorting, and search.
     */
    public function handle(?Request $request = null): LengthAwarePaginator
    {
        $request ??= request();

        $perPage = (int) ($request->input('per_page') ?? 10);
        $search = $request->input('search');
        $sort = $request->input('sort');

        $query = Role::query()->with('permissions');

        // Search query
        if (! empty($search) && is_string($search)) {
            $query->where(function (Builder $q) use ($search): void {
                $q->where('name', 'like', "%{$search}%");
            });
        }

        // Sorting
        $ordered = false;

        if (! empty($sort) && is_string($sort)) {
            $sortParts = explode('-', $sort);
            foreach ($sortParts as $part) {
                if (str_contains($part, '.')) {
                    [$column, $direction] = explode('.', $part, 2);
                    $direction = strtolower($direction) === 'desc' ? 'desc' : 'asc';
                    if (in_array($column, ['id', 'name', 'created_at', 'updated_at'], true)) {
                        $query->orderBy($column, $direction);
                        $ordered = true;
                    }
                }
            }
        }

        if (! $ordered) {
            $query->latest();
        }

        return $query->paginate($perPage);
    }
}

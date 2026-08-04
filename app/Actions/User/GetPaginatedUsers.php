<?php

declare(strict_types=1);

namespace App\Actions\User;

use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;

use function explode;
use function in_array;
use function is_string;
use function request;
use function str_contains;
use function strtolower;

class GetPaginatedUsers
{
    /**
     * Get paginated users with filtering, sorting, and search.
     */
    public function handle(?Request $request = null): LengthAwarePaginator
    {
        $request ??= request();

        /** @var int $perPage */
        $perPage = (int) ($request->input('per_page') ?? 10);
        $search = $request->input('search');
        $sort = $request->input('sort');
        $verified = $request->input('email_verified');

        $query = User::query();

        // Search query
        if (! empty($search) && is_string($search)) {
            $query->where(function ($q) use ($search): void {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Email verification filter
        if ($verified === 'verified') {
            $query->whereNotNull('email_verified_at');
        } elseif ($verified === 'unverified') {
            $query->whereNull('email_verified_at');
        }

        // Sorting
        if (! empty($sort) && is_string($sort)) {
            $sortParts = explode('-', $sort);
            foreach ($sortParts as $part) {
                if (str_contains($part, '.')) {
                    [$column, $direction] = explode('.', $part, 2);
                    $direction = strtolower($direction) === 'desc' ? 'desc' : 'asc';
                    if (in_array($column, ['id', 'uuid', 'name', 'email', 'created_at', 'email_verified_at'], true)) {
                        $query->orderBy($column, $direction);
                    }
                }
            }
        } else {
            $query->latest();
        }

        return $query->paginate($perPage);
    }
}

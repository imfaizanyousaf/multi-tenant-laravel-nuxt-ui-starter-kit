<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Visible;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\Permission\Models\Role as SpatieRole;

#[Visible([
    'id',
    'name',
    'guard_name',
    'created_at',
    'updated_at',
])]
final class Role extends SpatieRole
{
    use HasFactory;

    public const SUPER_ADMIN = 'Super Admin';
}

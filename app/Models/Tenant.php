<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\TenantStatus;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\Multitenancy\Models\Tenant as SpatieTenant;

#[Fillable(['name', 'domain', 'database', 'status'])]
class Tenant extends SpatieTenant
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'status' => TenantStatus::class,
        ];
    }
}

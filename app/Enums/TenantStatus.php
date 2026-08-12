<?php

declare(strict_types=1);

namespace App\Enums;

use function ucfirst;

enum TenantStatus: string
{
    case CREATING = 'creating';
    case ACTIVE = 'active';
    case FAILED = 'failed';

    public function label(): string
    {
        return ucfirst($this->value);
    }

    public function color(): string
    {
        return match ($this) {
            self::CREATING => 'warning',
            self::ACTIVE => 'success',
            self::FAILED => 'error',
        };
    }

    public function loading(): bool
    {
        return match ($this) {
            self::CREATING => true,
            self::ACTIVE => false,
            self::FAILED => false,
        };
    }
}

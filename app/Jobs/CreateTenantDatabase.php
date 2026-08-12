<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Enums\TenantStatus;
use App\Models\Tenant;
use Exception;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Spatie\Multitenancy\Jobs\NotTenantAware;

class CreateTenantDatabase implements NotTenantAware, ShouldQueue
{
    use Queueable;

    public function __construct(
        public Tenant $tenant
    ) {}

    public function handle(): void
    {
        try {
            DB::statement("CREATE DATABASE IF NOT EXISTS `{$this->tenant->database}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;");

            Artisan::call('tenants:artisan', [
                'artisanCommand' => 'migrate --database=tenant --seed',
                '--tenant' => $this->tenant->id,
            ]);

            $this->tenant->update(['status' => TenantStatus::ACTIVE]);
        } catch (Exception $e) {
            $this->tenant->update(['status' => TenantStatus::FAILED]);
            throw $e;
        }
    }
}

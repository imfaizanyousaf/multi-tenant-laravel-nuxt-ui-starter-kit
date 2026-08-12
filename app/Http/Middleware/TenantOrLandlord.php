<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Spatie\Multitenancy\Models\Tenant;

use function abort;
use function abort_unless;
use function config;
use function in_array;

class TenantOrLandlord
{
    public function handle(Request $request, Closure $next)
    {
        $host = $request->getHost();

        $isLandlordHost = in_array($host, ['localhost', '127.0.0.1', '[::1]'], true) || $host === config('app.domain', 'localhost');

        if (! $isLandlordHost) {
            abort_unless(Tenant::checkCurrent(), 404, 'Tenant not found');

            // Also apply EnsureValidTenantSession logic if needed, but let's keep it simple
            $tenantId = Tenant::current()->id;
            if (! $request->session()->has('tenant_id')) {
                $request->session()->put('tenant_id', $tenantId);
            } elseif ($request->session()->get('tenant_id') !== $tenantId) {
                abort(401, 'Invalid tenant session.');
            }
        }

        return $next($request);
    }
}

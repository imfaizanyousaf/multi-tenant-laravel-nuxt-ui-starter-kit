<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

use function abort_if;
use function config;
use function in_array;

class LandlordOnly
{
    public function handle(Request $request, Closure $next)
    {
        $host = $request->getHost();

        abort_if(
            ! in_array($host, ['localhost', '127.0.0.1', '[::1]'], true) && $host !== config('app.domain', 'localhost'),
            403,
            'This page is only available on the main domain.',
        );

        return $next($request);
    }
}

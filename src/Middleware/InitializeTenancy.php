<?php

declare(strict_types=1);

namespace Alcove\Middleware;

use Alcove\Exceptions\TenantNotFoundException;
use Alcove\Facades\Alcove;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class InitializeTenancy
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next, bool $required = true): Response
    {
        $tenant = Alcove::initialize($request);

        if ($required && $tenant === null) {
            throw new TenantNotFoundException(
                'Could not identify tenant for this request.'
            );
        }

        return $next($request);
    }

    /**
     * Handle tasks after the response has been sent to the browser.
     */
    public function terminate(Request $request, Response $response): void
    {
        Alcove::forgetTenant();
    }
}

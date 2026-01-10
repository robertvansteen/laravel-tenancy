<?php

declare(strict_types=1);

namespace Alcove\Testing;

use Alcove\Contracts\Tenant;
use Alcove\Facades\Alcove;

/**
 * Test helpers for tenant-aware tests.
 */
trait TenantTestHelpers
{
    /**
     * Set the current tenant for testing.
     */
    protected function actingAsTenant(Tenant $tenant): static
    {
        Alcove::setTenant($tenant);

        return $this;
    }

    /**
     * Clear the tenant context.
     */
    protected function withoutTenant(): static
    {
        Alcove::forget();

        return $this;
    }

    /**
     * Run a callback as a specific tenant.
     *
     * @template T
     *
     * @param  \Closure(Tenant): T  $callback
     * @return T
     */
    protected function asTenant(Tenant $tenant, \Closure $callback): mixed
    {
        return Alcove::run($tenant, $callback);
    }

    /**
     * Add tenant header to a request.
     *
     * @param  array<string, string>  $headers
     * @return array<string, string>
     */
    protected function withTenantHeader(Tenant $tenant, array $headers = []): array
    {
        return array_merge($headers, [
            'X-Tenant-ID' => (string) $tenant->getTenantKey(),
        ]);
    }
}

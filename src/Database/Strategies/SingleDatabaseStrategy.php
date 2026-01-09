<?php

declare(strict_types=1);

namespace Alcove\Database\Strategies;

use Alcove\Contracts\Tenant;

/**
 * Single database strategy - all tenants share the same database.
 * Tenant isolation is handled by model scopes using a tenant_id column.
 */
class SingleDatabaseStrategy implements DatabaseStrategy
{
    public function __construct(
        protected string $connectionName = 'mysql',
    ) {}

    public function connect(Tenant $tenant): void
    {
        // No connection switching needed for single database strategy.
        // Tenant scoping is handled by model traits.
    }

    public function disconnect(): void
    {
        // No-op for single database strategy.
    }

    public function getConnectionName(Tenant $tenant): string
    {
        return $this->connectionName;
    }
}

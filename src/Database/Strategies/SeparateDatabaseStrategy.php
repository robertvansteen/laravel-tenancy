<?php

declare(strict_types=1);

namespace Alcove\Database\Strategies;

use Alcove\Contracts\Tenant;
use Illuminate\Support\Facades\DB;

/**
 * Separate database strategy - each tenant has their own database.
 * Creates a dynamic connection for each tenant.
 */
class SeparateDatabaseStrategy implements DatabaseStrategy
{
    protected string $tenantConnectionName = 'tenant';

    public function __construct(
        protected string $databasePrefix = 'tenant_',
        protected string $baseConnection = 'mysql',
    ) {}

    public function connect(Tenant $tenant): void
    {
        $baseConfig = config("database.connections.{$this->baseConnection}");

        if (! is_array($baseConfig)) {
            return;
        }

        $tenantConfig = array_merge($baseConfig, [
            'database' => $this->getDatabaseName($tenant),
        ]);

        config([
            "database.connections.{$this->tenantConnectionName}" => $tenantConfig,
        ]);

        DB::purge($this->tenantConnectionName);
        DB::reconnect($this->tenantConnectionName);
    }

    public function disconnect(): void
    {
        DB::purge($this->tenantConnectionName);
    }

    public function getConnectionName(Tenant $tenant): string
    {
        return $this->tenantConnectionName;
    }

    /**
     * Get the database name for a tenant.
     */
    protected function getDatabaseName(Tenant $tenant): string
    {
        return $this->databasePrefix.$tenant->getTenantKey();
    }

    /**
     * Set the tenant connection name.
     */
    public function setTenantConnectionName(string $name): static
    {
        $this->tenantConnectionName = $name;

        return $this;
    }
}

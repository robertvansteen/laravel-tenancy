<?php

declare(strict_types=1);

namespace Alcove\Database;

use Alcove\Contracts\Tenant;
use Alcove\Database\Strategies\DatabaseStrategy;

class TenantDatabaseManager
{
    protected ?Tenant $currentTenant = null;

    public function __construct(
        protected DatabaseStrategy $strategy,
    ) {}

    /**
     * Switch to a tenant's database.
     */
    public function switchToTenant(Tenant $tenant): void
    {
        $this->strategy->connect($tenant);
        $this->currentTenant = $tenant;
    }

    /**
     * Switch back to the central database.
     */
    public function switchToCentral(): void
    {
        $this->strategy->disconnect();
        $this->currentTenant = null;
    }

    /**
     * Get the current tenant's connection name.
     */
    public function getCurrentConnectionName(): ?string
    {
        if ($this->currentTenant === null) {
            return null;
        }

        return $this->strategy->getConnectionName($this->currentTenant);
    }

    /**
     * Get the current tenant.
     */
    public function getCurrentTenant(): ?Tenant
    {
        return $this->currentTenant;
    }

    /**
     * Get the database strategy.
     */
    public function getStrategy(): DatabaseStrategy
    {
        return $this->strategy;
    }
}

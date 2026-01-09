<?php

declare(strict_types=1);

namespace Alcove\Database\Strategies;

use Alcove\Contracts\Tenant;

interface DatabaseStrategy
{
    /**
     * Connect to the tenant's database.
     */
    public function connect(Tenant $tenant): void;

    /**
     * Disconnect from the tenant's database.
     */
    public function disconnect(): void;

    /**
     * Get the connection name for a tenant.
     */
    public function getConnectionName(Tenant $tenant): string;
}

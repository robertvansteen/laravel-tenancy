<?php

declare(strict_types=1);

namespace Alcove\Contracts;

interface Tenant
{
    /**
     * Get the tenant's primary key value.
     */
    public function getTenantKey(): string|int;

    /**
     * Get the tenant's primary key name.
     */
    public function getTenantKeyName(): string;

    /**
     * Get the tenant's identifier (slug, domain, etc.).
     */
    public function getTenantIdentifier(): string;
}

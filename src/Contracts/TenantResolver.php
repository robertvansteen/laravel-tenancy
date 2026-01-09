<?php

declare(strict_types=1);

namespace Alcove\Contracts;

interface TenantResolver
{
    /**
     * Attempt to resolve a tenant from the current context.
     */
    public function resolve(): ?Tenant;

    /**
     * Check if this resolver can attempt resolution.
     */
    public function canResolve(): bool;
}

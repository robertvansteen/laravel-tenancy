<?php

declare(strict_types=1);

namespace Alcove\Contracts;

interface TenantResolver
{
    /**
     * Attempt to resolve a tenant from the given request.
     */
    public function resolve(\Illuminate\Http\Request $request): ?Tenant;

    /**
     * Check if this resolver can attempt resolution for the given request.
     */
    public function canResolve(\Illuminate\Http\Request $request): bool;
}

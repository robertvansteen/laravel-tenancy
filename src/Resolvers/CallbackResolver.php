<?php

declare(strict_types=1);

namespace Alcove\Resolvers;

use Alcove\Contracts\Tenant;
use Alcove\Contracts\TenantResolver;
use Closure;

class CallbackResolver implements TenantResolver
{
    /**
     * @param  Closure(\Illuminate\Http\Request): (Tenant|null)  $resolver
     * @param  (Closure(\Illuminate\Http\Request): bool)|null  $canResolveCallback
     */
    public function __construct(
        protected Closure $resolver,
        protected ?Closure $canResolveCallback = null,
    ) {}

    public function resolve(\Illuminate\Http\Request $request): ?Tenant
    {
        return ($this->resolver)($request);
    }

    public function canResolve(\Illuminate\Http\Request $request): bool
    {
        if ($this->canResolveCallback !== null) {
            return ($this->canResolveCallback)($request);
        }

        return true;
    }
}

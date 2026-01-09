<?php

declare(strict_types=1);

namespace Alcove\Resolvers;

use Alcove\Contracts\Tenant;
use Alcove\Contracts\TenantResolver;
use Closure;

class CallbackResolver implements TenantResolver
{
    /**
     * @param  Closure(): (Tenant|null)  $resolver
     * @param  (Closure(): bool)|null  $canResolveCallback
     */
    public function __construct(
        protected Closure $resolver,
        protected ?Closure $canResolveCallback = null,
    ) {}

    public function resolve(): ?Tenant
    {
        return ($this->resolver)();
    }

    public function canResolve(): bool
    {
        if ($this->canResolveCallback !== null) {
            return ($this->canResolveCallback)();
        }

        return true;
    }
}

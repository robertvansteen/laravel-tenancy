<?php

declare(strict_types=1);

namespace Alcove\Resolvers;

use Alcove\Contracts\Tenant;
use Alcove\Contracts\TenantResolver;

class ResolverPipeline implements TenantResolver
{
    /** @var array<int, TenantResolver> */
    protected array $resolvers = [];

    /**
     * @param  array<int, TenantResolver>  $resolvers
     */
    public function __construct(array $resolvers = [])
    {
        $this->resolvers = $resolvers;
    }

    /**
     * Add a resolver to the pipeline.
     */
    public function pipe(TenantResolver $resolver): static
    {
        $this->resolvers[] = $resolver;

        return $this;
    }

    /**
     * Resolve a tenant by trying each resolver in order.
     */
    public function resolve(): ?Tenant
    {
        foreach ($this->resolvers as $resolver) {
            if ($resolver->canResolve() && $tenant = $resolver->resolve()) {
                return $tenant;
            }
        }

        return null;
    }

    /**
     * Check if any resolver can attempt resolution.
     */
    public function canResolve(): bool
    {
        foreach ($this->resolvers as $resolver) {
            if ($resolver->canResolve()) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get all resolvers in the pipeline.
     *
     * @return array<int, TenantResolver>
     */
    public function getResolvers(): array
    {
        return $this->resolvers;
    }
}

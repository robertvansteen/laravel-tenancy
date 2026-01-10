<?php

declare(strict_types=1);

namespace Alcove;

use Alcove\Contracts\Tenant;
use Alcove\Contracts\TenantResolver;
use Alcove\Database\TenantDatabaseManager;
use Alcove\Events\TenantChanged;
use Alcove\Events\TenantIdentified;
use Alcove\Exceptions\TenantContextException;
use Closure;

class Alcove
{
    protected ?Tenant $tenant = null;

    protected bool $initialized = false;

    public function __construct(
        protected TenantResolver $resolver,
        protected TenantDatabaseManager $databaseManager,
    ) {}

    /**
     * Add a resolver to the pipeline.
     */
    public function addResolver(TenantResolver $resolver): static
    {
        if ($this->resolver instanceof Resolvers\ResolverPipeline) {
            $this->resolver->pipe($resolver);
        }

        return $this;
    }

    /**
     * Initialize tenancy from the given request context.
     */
    public function initialize(\Illuminate\Http\Request $request): ?Tenant
    {
        if ($this->initialized) {
            return $this->tenant;
        }

        $this->tenant = $this->resolver->resolve($request);
        $this->initialized = true;

        if ($this->tenant) {
            event(new TenantIdentified($this->tenant));
        }

        return $this->tenant;
    }

    /**
     * Set the current tenant explicitly.
     */
    public function setTenant(Tenant $tenant): static
    {
        $previousTenant = $this->tenant;
        $this->tenant = $tenant;
        $this->initialized = true;

        event(new TenantChanged($tenant, $previousTenant));

        return $this;
    }

    /**
     * Forget the current tenant.
     */
    public function forgetTenant(): static
    {
        $previousTenant = $this->tenant;
        $this->tenant = null;
        $this->initialized = false;

        if ($previousTenant !== null) {
            event(new TenantChanged(null, $previousTenant));
        }

        return $this;
    }

    /**
     * Get the current tenant.
     */
    public function tenant(): ?Tenant
    {
        return $this->tenant;
    }

    /**
     * Get the current tenant or fail.
     *
     * @throws TenantContextException
     */
    public function tenantOrFail(): Tenant
    {
        return $this->tenant ?? throw new TenantContextException(
            'No tenant context available.'
        );
    }

    /**
     * Check if tenant context is active.
     */
    public function hasTenant(): bool
    {
        return $this->tenant !== null;
    }

    /**
     * Run a callback within a specific tenant context.
     *
     * @template T
     *
     * @param  Closure(Tenant): T  $callback
     * @return T
     */
    public function run(Tenant $tenant, Closure $callback): mixed
    {
        $previousTenant = $this->tenant;

        try {
            $this->setTenant($tenant);

            return $callback($tenant);
        } finally {
            $this->setTenant($previousTenant);
        }
    }

    /**
     * Run a callback without tenant context.
     *
     * @template T
     *
     * @param  Closure(): T  $callback
     * @return T
     */
    public function runWithoutTenant(Closure $callback): mixed
    {
        $previousTenant = $this->tenant;

        try {
            $this->forgetTenant();

            return $callback();
        } finally {
            if ($previousTenant !== null) {
                $this->setTenant($previousTenant);
            }
        }
    }

    /**
     * Clear tenant context.
     *
     * @deprecated Use forgetTenant() instead
     */
    public function forget(): static
    {
        return $this->forgetTenant();
    }

    /**
     * Check if tenancy has been initialized.
     */
    public function isInitialized(): bool
    {
        return $this->initialized;
    }
}

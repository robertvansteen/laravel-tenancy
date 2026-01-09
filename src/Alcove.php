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

    /** @var array<int, Closure> */
    protected array $onTenantCallbacks = [];

    public function __construct(
        protected TenantResolver $resolver,
        protected TenantDatabaseManager $databaseManager,
    ) {}

    /**
     * Initialize tenancy from the current request context.
     */
    public function initialize(): ?Tenant
    {
        if ($this->initialized) {
            return $this->tenant;
        }

        $this->tenant = $this->resolver->resolve();
        $this->initialized = true;

        if ($this->tenant) {
            $this->bootstrap($this->tenant);
            event(new TenantIdentified($this->tenant));
        }

        return $this->tenant;
    }

    /**
     * Set the current tenant explicitly.
     */
    public function setTenant(?Tenant $tenant): static
    {
        $previousTenant = $this->tenant;
        $this->tenant = $tenant;
        $this->initialized = true;

        if ($tenant) {
            $this->bootstrap($tenant);
        } else {
            $this->databaseManager->switchToCentral();
        }

        if ($previousTenant !== $tenant) {
            event(new TenantChanged($tenant, $previousTenant));
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
            $this->setTenant(null);

            return $callback();
        } finally {
            $this->setTenant($previousTenant);
        }
    }

    /**
     * Bootstrap tenant-specific services.
     */
    protected function bootstrap(Tenant $tenant): void
    {
        $this->databaseManager->switchToTenant($tenant);

        foreach ($this->onTenantCallbacks as $callback) {
            $callback($tenant);
        }
    }

    /**
     * Register a callback to run when tenant is set.
     */
    public function onTenant(Closure $callback): static
    {
        $this->onTenantCallbacks[] = $callback;

        return $this;
    }

    /**
     * Clear tenant context.
     */
    public function forget(): static
    {
        $this->tenant = null;
        $this->initialized = false;
        $this->databaseManager->switchToCentral();

        return $this;
    }

    /**
     * Check if tenancy has been initialized.
     */
    public function isInitialized(): bool
    {
        return $this->initialized;
    }
}

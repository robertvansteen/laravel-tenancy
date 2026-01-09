<?php

declare(strict_types=1);

namespace Alcove\Resolvers;

use Alcove\Contracts\Tenant;
use Alcove\Contracts\TenantResolver;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class SubdomainResolver implements TenantResolver
{
    /** @var array<int, string> */
    protected array $excludedSubdomains = ['www', 'api', 'admin'];

    /**
     * @param  class-string<Model&Tenant>  $tenantModel
     */
    public function __construct(
        protected Request $request,
        protected string $tenantModel,
        protected string $baseDomain,
        protected string $identifierColumn = 'slug',
    ) {}

    public function resolve(): ?Tenant
    {
        $subdomain = $this->extractSubdomain();

        if ($subdomain === null || in_array($subdomain, $this->excludedSubdomains, true)) {
            return null;
        }

        /** @var (Model&Tenant)|null $tenant */
        $tenant = $this->tenantModel::query()
            ->where($this->identifierColumn, $subdomain)
            ->first();

        return $tenant;
    }

    public function canResolve(): bool
    {
        return $this->extractSubdomain() !== null;
    }

    /**
     * Set subdomains to exclude from resolution.
     *
     * @param  array<int, string>  $subdomains
     */
    public function setExcludedSubdomains(array $subdomains): static
    {
        $this->excludedSubdomains = $subdomains;

        return $this;
    }

    protected function extractSubdomain(): ?string
    {
        $host = $this->request->getHost();
        $baseDomain = ltrim($this->baseDomain, '.');

        if (! str_ends_with($host, $baseDomain)) {
            return null;
        }

        $subdomain = rtrim(str_replace($baseDomain, '', $host), '.');

        return $subdomain !== '' ? $subdomain : null;
    }
}

<?php

declare(strict_types=1);

namespace Alcove\Resolvers;

use Alcove\Contracts\Tenant;
use Alcove\Contracts\TenantResolver;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class PathPrefixResolver implements TenantResolver
{
    /**
     * @param  class-string<Model&Tenant>  $tenantModel
     */
    public function __construct(
        protected Request $request,
        protected string $tenantModel,
        protected string $identifierColumn = 'slug',
    ) {}

    public function resolve(): ?Tenant
    {
        $prefix = $this->extractPrefix();

        if ($prefix === null) {
            return null;
        }

        /** @var (Model&Tenant)|null $tenant */
        $tenant = $this->tenantModel::query()
            ->where($this->identifierColumn, $prefix)
            ->first();

        return $tenant;
    }

    public function canResolve(): bool
    {
        return $this->extractPrefix() !== null;
    }

    /**
     * Get the tenant identifier from the URL path.
     */
    public function getPrefix(): ?string
    {
        return $this->extractPrefix();
    }

    protected function extractPrefix(): ?string
    {
        $segments = $this->request->segments();

        return $segments[0] ?? null;
    }
}

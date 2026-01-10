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
        protected string $tenantModel,
        protected string $identifierColumn = 'slug',
    ) {}

    public function resolve(Request $request): ?Tenant
    {
        $prefix = $this->extractPrefix($request);

        if ($prefix === null) {
            return null;
        }

        /** @var (Model&Tenant)|null $tenant */
        $tenant = $this->tenantModel::query()
            ->where($this->identifierColumn, $prefix)
            ->first();

        return $tenant;
    }

    public function canResolve(Request $request): bool
    {
        return $this->extractPrefix($request) !== null;
    }

    /**
     * Get the tenant identifier from the URL path.
     */
    public function getPrefix(Request $request): ?string
    {
        return $this->extractPrefix($request);
    }

    protected function extractPrefix(Request $request): ?string
    {
        $segments = $request->segments();

        return $segments[0] ?? null;
    }
}

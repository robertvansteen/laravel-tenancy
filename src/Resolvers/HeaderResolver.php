<?php

declare(strict_types=1);

namespace Alcove\Resolvers;

use Alcove\Contracts\Tenant;
use Alcove\Contracts\TenantResolver;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class HeaderResolver implements TenantResolver
{
    /**
     * @param  class-string<Model&Tenant>  $tenantModel
     */
    public function __construct(
        protected Request $request,
        protected string $tenantModel,
        protected string $header = 'X-Tenant-ID',
        protected string $identifierColumn = 'id',
    ) {}

    public function resolve(): ?Tenant
    {
        $identifier = $this->request->header($this->header);

        if ($identifier === null || $identifier === '') {
            return null;
        }

        /** @var (Model&Tenant)|null $tenant */
        $tenant = $this->tenantModel::query()
            ->where($this->identifierColumn, $identifier)
            ->first();

        return $tenant;
    }

    public function canResolve(): bool
    {
        return $this->request->hasHeader($this->header);
    }
}

<?php

declare(strict_types=1);

namespace Alcove\Facades;

use Alcove\Alcove as AlcoveService;
use Alcove\Contracts\Tenant;
use Closure;
use Illuminate\Support\Facades\Facade;

/**
 * @method static Tenant|null initialize()
 * @method static static setTenant(Tenant|null $tenant)
 * @method static Tenant|null tenant()
 * @method static Tenant tenantOrFail()
 * @method static bool hasTenant()
 * @method static mixed run(Tenant $tenant, Closure $callback)
 * @method static mixed runWithoutTenant(Closure $callback)
 * @method static static onTenant(Closure $callback)
 * @method static static forget()
 * @method static bool isInitialized()
 *
 * @see \Alcove\Alcove
 */
class Alcove extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return AlcoveService::class;
    }
}

<?php

declare(strict_types=1);

namespace Alcove\Models\Concerns;

use Alcove\Scopes\TenantScope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * Automatic global scope trait.
 * Use this when you want automatic tenant filtering on all queries.
 *
 * @mixin Model
 */
trait HasTenantScope
{
    use BelongsToTenant;

    /**
     * Boot the trait - add the global tenant scope.
     */
    public static function bootHasTenantScope(): void
    {
        static::addGlobalScope(new TenantScope);
    }

    /**
     * Query without tenant scope.
     *
     * @param  Builder<Model>  $query
     * @return Builder<Model>
     */
    public function scopeWithoutTenantScope(Builder $query): Builder
    {
        return $query->withoutGlobalScope(TenantScope::class);
    }
}

<?php

declare(strict_types=1);

namespace Alcove\Scopes;

use Alcove\Contracts\Tenant;
use Alcove\Facades\Alcove;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class TenantScope implements Scope
{
    /**
     * Apply the scope to a given Eloquent query builder.
     *
     * @param  Builder<Model>  $builder
     */
    public function apply(Builder $builder, Model $model): void
    {
        if (Alcove::hasTenant() && method_exists($model, 'getTenantKeyColumn')) {
            $builder->where(
                $model->qualifyColumn($model->getTenantKeyColumn()),
                Alcove::tenant()?->getTenantKey()
            );
        }
    }

    /**
     * Extend the query builder with the needed functions.
     *
     * @param  Builder<Model>  $builder
     */
    public function extend(Builder $builder): void
    {
        $builder->macro('withoutTenant', function (Builder $builder) {
            return $builder->withoutGlobalScope(TenantScope::class);
        });

        $builder->macro('forTenant', function (Builder $builder, Tenant $tenant) {
            $model = $builder->getModel();

            if (! method_exists($model, 'getTenantKeyColumn')) {
                return $builder;
            }

            return $builder->withoutGlobalScope(TenantScope::class)
                ->where(
                    $model->qualifyColumn($model->getTenantKeyColumn()),
                    $tenant->getTenantKey()
                );
        });
    }
}

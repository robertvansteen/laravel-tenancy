<?php

declare(strict_types=1);

namespace Alcove\Models\Concerns;

use Alcove\Contracts\Tenant;
use Alcove\Facades\Alcove;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Explicit tenant scoping trait.
 * Use this when you want full control over when tenant scoping is applied.
 *
 * @mixin Model
 */
trait BelongsToTenant
{
    /**
     * Boot the trait - automatically assign tenant_id on creation.
     */
    public static function bootBelongsToTenant(): void
    {
        static::creating(function (Model $model): void {
            /** @var Model&BelongsToTenant $model */
            $column = $model->getTenantKeyColumn();

            if (Alcove::hasTenant() && empty($model->{$column})) {
                $model->{$column} = Alcove::tenant()?->getTenantKey();
            }
        });
    }

    /**
     * Get the tenant relationship.
     *
     * @return BelongsTo<Model&Tenant, $this>
     */
    public function tenant(): BelongsTo
    {
        /** @var class-string<Model&Tenant> $tenantModel */
        $tenantModel = config('alcove.tenant_model');

        return $this->belongsTo($tenantModel, $this->getTenantKeyColumn());
    }

    /**
     * Get the tenant key column name.
     */
    public function getTenantKeyColumn(): string
    {
        /** @var string|null $column */
        $column = property_exists($this, 'tenantKeyColumn') ? $this->tenantKeyColumn : null;

        return $column ?? config('alcove.tenant_key_column', 'tenant_id');
    }

    /**
     * Scope to a specific tenant.
     *
     * @param  Builder<Model>  $query
     * @return Builder<Model>
     */
    public function scopeForTenant(Builder $query, Tenant $tenant): Builder
    {
        return $query->where(
            $this->qualifyColumn($this->getTenantKeyColumn()),
            $tenant->getTenantKey()
        );
    }

    /**
     * Scope to the current tenant.
     *
     * @param  Builder<Model>  $query
     * @return Builder<Model>
     */
    public function scopeForCurrentTenant(Builder $query): Builder
    {
        if (Alcove::hasTenant()) {
            return $query->where(
                $this->qualifyColumn($this->getTenantKeyColumn()),
                Alcove::tenant()?->getTenantKey()
            );
        }

        return $query;
    }

    /**
     * Check if model belongs to given tenant.
     */
    public function belongsToTenant(Tenant $tenant): bool
    {
        return $this->{$this->getTenantKeyColumn()} === $tenant->getTenantKey();
    }

    /**
     * Check if model belongs to current tenant.
     */
    public function belongsToCurrentTenant(): bool
    {
        if (! Alcove::hasTenant()) {
            return false;
        }

        return $this->belongsToTenant(Alcove::tenantOrFail());
    }
}

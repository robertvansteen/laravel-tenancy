<?php

declare(strict_types=1);

namespace Alcove\Models;

use Alcove\Contracts\Tenant as TenantContract;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $name
 * @property string $slug
 * @property string|null $domain
 * @property string|null $database
 * @property array<string, mixed>|null $data
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 */
class Tenant extends Model implements TenantContract
{
    /** @var list<string> */
    protected $fillable = [
        'name',
        'slug',
        'domain',
        'database',
        'data',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'data' => 'array',
        ];
    }

    public function getTenantKey(): string|int
    {
        return $this->getKey();
    }

    public function getTenantKeyName(): string
    {
        return $this->getKeyName();
    }

    public function getTenantIdentifier(): string
    {
        return $this->slug ?? $this->domain ?? (string) $this->getKey();
    }
}

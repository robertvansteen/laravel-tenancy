<?php

declare(strict_types=1);

namespace Alcove\Commands;

use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Model;

class TenantListCommand extends Command
{
    /** @var string */
    protected $signature = 'tenant:list';

    /** @var string */
    protected $description = 'List all tenants';

    public function handle(): int
    {
        /** @var class-string<Model> $tenantModel */
        $tenantModel = config('alcove.tenant_model');

        $tenants = $tenantModel::all();

        if ($tenants->isEmpty()) {
            $this->components->info('No tenants found.');

            return self::SUCCESS;
        }

        $this->components->info("Found {$tenants->count()} tenant(s):");

        $headers = ['ID', 'Name', 'Slug', 'Domain', 'Created At'];

        $rows = $tenants->map(function (Model $tenant): array {
            return [
                $tenant->getKey(),
                $tenant->getAttribute('name'),
                $tenant->getAttribute('slug'),
                $tenant->getAttribute('domain') ?? '-',
                $tenant->getAttribute('created_at')?->format('Y-m-d H:i:s') ?? '-',
            ];
        })->toArray();

        $this->table($headers, $rows);

        return self::SUCCESS;
    }
}

<?php

declare(strict_types=1);

namespace Alcove\Commands;

use Alcove\Contracts\Tenant;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class TenantCreateCommand extends Command
{
    /** @var string */
    protected $signature = 'tenant:create
                            {name? : The tenant name}
                            {--slug= : The tenant slug (generated from name if not provided)}
                            {--domain= : The tenant domain (optional)}';

    /** @var string */
    protected $description = 'Create a new tenant';

    public function handle(): int
    {
        /** @var class-string<Model&Tenant> $tenantModel */
        $tenantModel = config('alcove.tenant_model');

        $name = $this->argument('name') ?? $this->ask('What is the tenant name?');

        if (empty($name)) {
            $this->components->error('Tenant name is required.');

            return self::FAILURE;
        }

        $slug = $this->option('slug') ?? Str::slug($name);
        $domain = $this->option('domain');

        // Check if slug already exists
        if ($tenantModel::where('slug', $slug)->exists()) {
            $this->components->error("A tenant with slug '{$slug}' already exists.");

            return self::FAILURE;
        }

        // Check if domain already exists (if provided)
        if ($domain !== null && $tenantModel::where('domain', $domain)->exists()) {
            $this->components->error("A tenant with domain '{$domain}' already exists.");

            return self::FAILURE;
        }

        $tenant = $tenantModel::create([
            'name' => $name,
            'slug' => $slug,
            'domain' => $domain,
        ]);

        $this->components->info("Tenant '{$name}' created successfully!");

        $this->table(
            ['ID', 'Name', 'Slug', 'Domain'],
            [[
                $tenant->getKey(),
                $tenant->getAttribute('name'),
                $tenant->getAttribute('slug'),
                $tenant->getAttribute('domain') ?? '-',
            ]]
        );

        return self::SUCCESS;
    }
}

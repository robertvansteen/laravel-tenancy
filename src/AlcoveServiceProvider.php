<?php

declare(strict_types=1);

namespace Alcove;

use Alcove\Commands\TenantCreateCommand;
use Alcove\Commands\TenantListCommand;
use Alcove\Contracts\TenantResolver;
use Alcove\Database\Strategies\DatabaseStrategy;
use Alcove\Database\Strategies\SeparateDatabaseStrategy;
use Alcove\Database\Strategies\SingleDatabaseStrategy;
use Alcove\Database\TenantDatabaseManager;
use Alcove\Middleware\InitializeTenancy;
use Alcove\Resolvers\CallbackResolver;
use Alcove\Resolvers\HeaderResolver;
use Alcove\Resolvers\PathPrefixResolver;
use Alcove\Resolvers\ResolverPipeline;
use Alcove\Resolvers\SubdomainResolver;
use Illuminate\Contracts\Foundation\Application;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class AlcoveServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('alcove')
            ->hasConfigFile()
            ->hasMigration('create_tenants_table')
            ->hasCommands([
                TenantListCommand::class,
                TenantCreateCommand::class,
            ]);
    }

    public function packageRegistered(): void
    {
        $this->registerDatabaseStrategy();
        $this->registerDatabaseManager();
        $this->registerResolverPipeline();
        $this->registerAlcove();
    }

    public function packageBooted(): void
    {
        $this->registerMiddleware();
    }

    protected function registerDatabaseStrategy(): void
    {
        $this->app->singleton(DatabaseStrategy::class, function (Application $app): DatabaseStrategy {
            /** @var string $strategy */
            $strategy = config('alcove.database.strategy', 'single');

            /** @var string $connection */
            $connection = config('alcove.database.connection', 'mysql');

            /** @var string $prefix */
            $prefix = config('alcove.database.prefix', 'tenant_');

            return match ($strategy) {
                'separate' => new SeparateDatabaseStrategy($prefix, $connection),
                default => new SingleDatabaseStrategy($connection),
            };
        });
    }

    protected function registerDatabaseManager(): void
    {
        $this->app->singleton(TenantDatabaseManager::class, function (Application $app): TenantDatabaseManager {
            return new TenantDatabaseManager(
                $app->make(DatabaseStrategy::class)
            );
        });
    }

    protected function registerResolverPipeline(): void
    {
        $this->app->singleton(TenantResolver::class, function (Application $app): TenantResolver {
            $pipeline = new ResolverPipeline;

            /** @var class-string<\Alcove\Contracts\Tenant&\Illuminate\Database\Eloquent\Model> $tenantModel */
            $tenantModel = config('alcove.tenant_model', Models\Tenant::class);

            /** @var array<int, array<string, mixed>> $resolverConfigs */
            $resolverConfigs = config('alcove.resolvers', []);

            foreach ($resolverConfigs as $resolverConfig) {
                $resolver = $this->createResolver($app, $tenantModel, $resolverConfig);

                if ($resolver !== null) {
                    $pipeline->pipe($resolver);
                }
            }

            return $pipeline;
        });
    }

    /**
     * @param  class-string<\Alcove\Contracts\Tenant&\Illuminate\Database\Eloquent\Model>  $tenantModel
     * @param  array<string, mixed>  $config
     */
    protected function createResolver(Application $app, string $tenantModel, array $config): ?TenantResolver
    {
        /** @var string $driver */
        $driver = $config['driver'] ?? '';

        return match ($driver) {
            'header' => new HeaderResolver(
                $app->make('request'),
                $tenantModel,
                $config['header'] ?? 'X-Tenant-ID',
                $config['column'] ?? 'id',
            ),
            'subdomain' => new SubdomainResolver(
                $app->make('request'),
                $tenantModel,
                $config['domain'] ?? config('app.url', 'localhost'),
                $config['column'] ?? 'slug',
            ),
            'path' => new PathPrefixResolver(
                $app->make('request'),
                $tenantModel,
                $config['column'] ?? 'slug',
            ),
            'callback' => isset($config['resolver']) && is_callable($config['resolver'])
                ? new CallbackResolver(
                    $config['resolver'],
                    $config['canResolve'] ?? null,
                )
                : null,
            default => null,
        };
    }

    protected function registerAlcove(): void
    {
        $this->app->singleton(Alcove::class, function (Application $app): Alcove {
            return new Alcove(
                $app->make(TenantResolver::class),
                $app->make(TenantDatabaseManager::class),
            );
        });

        $this->app->alias(Alcove::class, 'alcove');
    }

    protected function registerMiddleware(): void
    {
        /** @var \Illuminate\Routing\Router $router */
        $router = $this->app->make('router');

        $router->aliasMiddleware('tenant', InitializeTenancy::class);
    }
}

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
use Alcove\Resolvers\ResolverPipeline;
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
        $this->registerEventListeners();
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
            return new ResolverPipeline;
        });
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

    protected function registerEventListeners(): void
    {
        /** @var \Illuminate\Events\Dispatcher $events */
        $events = $this->app->make('events');

        $events->subscribe(Listeners\BootstrapTenancy::class);
    }
}

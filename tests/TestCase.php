<?php

declare(strict_types=1);

namespace Alcove\Tests;

use Alcove\AlcoveServiceProvider;
use Alcove\Testing\TenantTestHelpers;
use Illuminate\Database\Eloquent\Factories\Factory;
use Orchestra\Testbench\TestCase as Orchestra;

class TestCase extends Orchestra
{
    use TenantTestHelpers;

    protected function setUp(): void
    {
        parent::setUp();

        Factory::guessFactoryNamesUsing(
            fn (string $modelName) => 'Alcove\\Database\\Factories\\'.class_basename($modelName).'Factory'
        );
    }

    /**
     * @return array<int, class-string>
     */
    protected function getPackageProviders($app): array
    {
        return [
            AlcoveServiceProvider::class,
        ];
    }

    /**
     * @param  \Illuminate\Foundation\Application  $app
     */
    public function getEnvironmentSetUp($app): void
    {
        config()->set('database.default', 'testing');

        // Run migrations
        $migration = include __DIR__.'/../database/migrations/create_tenants_table.php.stub';
        $migration->up();
    }
}

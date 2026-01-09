<?php

namespace Alcove\Alcove;

use Alcove\Alcove\Commands\AlcoveCommand;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class AlcoveServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        /*
         * This class is a Package Service Provider
         *
         * More info: https://github.com/spatie/laravel-package-tools
         */
        $package
            ->name('alcove')
            ->hasConfigFile()
            ->hasViews()
            ->hasMigration('create_alcove_table')
            ->hasCommand(AlcoveCommand::class);
    }
}

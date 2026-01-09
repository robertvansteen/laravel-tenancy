<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Tenant Model
    |--------------------------------------------------------------------------
    |
    | The model class that represents a tenant. You can extend the default
    | Tenant model or create your own implementing the Tenant contract.
    |
    */

    'tenant_model' => Alcove\Models\Tenant::class,

    /*
    |--------------------------------------------------------------------------
    | Tenant Resolvers
    |--------------------------------------------------------------------------
    |
    | Configure how tenants are identified. Resolvers are tried in order
    | until one successfully identifies a tenant. The header resolver is
    | great for local development and APIs.
    |
    | Available drivers: header, subdomain, path, callback
    |
    */

    'resolvers' => [
        [
            'driver' => 'header',
            'header' => 'X-Tenant-ID',
            'column' => 'id',
        ],
        // [
        //     'driver' => 'subdomain',
        //     'domain' => env('APP_DOMAIN', 'localhost'),
        //     'column' => 'slug',
        // ],
        // [
        //     'driver' => 'path',
        //     'column' => 'slug',
        // ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Database Strategy
    |--------------------------------------------------------------------------
    |
    | Choose how tenant data is stored:
    |
    | - 'single': All tenants share one database (uses tenant_id column)
    | - 'separate': Each tenant has their own database
    |
    */

    'database' => [
        'strategy' => env('ALCOVE_DATABASE_STRATEGY', 'single'),
        'connection' => env('DB_CONNECTION', 'mysql'),
        'prefix' => env('ALCOVE_DATABASE_PREFIX', 'tenant_'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Tenant Key Column
    |--------------------------------------------------------------------------
    |
    | The default column name used for tenant foreign keys in your models.
    | This can be overridden per-model using the $tenantKeyColumn property.
    |
    */

    'tenant_key_column' => 'tenant_id',

];

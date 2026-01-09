<?php

use Alcove\Facades\Alcove;
use Alcove\Models\Tenant;

it('can set and get a tenant', function () {
    $tenant = Tenant::create([
        'name' => 'Acme Corp',
        'slug' => 'acme-corp',
    ]);

    Alcove::setTenant($tenant);

    expect(Alcove::hasTenant())->toBeTrue();
    expect(Alcove::tenant())->toBe($tenant);
});

it('can forget a tenant', function () {
    $tenant = Tenant::create([
        'name' => 'Acme Corp',
        'slug' => 'acme-corp',
    ]);

    Alcove::setTenant($tenant);
    Alcove::forget();

    expect(Alcove::hasTenant())->toBeFalse();
    expect(Alcove::tenant())->toBeNull();
});

it('can run a callback in tenant context', function () {
    $tenant = Tenant::create([
        'name' => 'Acme Corp',
        'slug' => 'acme-corp',
    ]);

    $result = Alcove::run($tenant, function ($t) {
        return $t->name;
    });

    expect($result)->toBe('Acme Corp');
});

it('restores previous tenant after run', function () {
    $tenant1 = Tenant::create([
        'name' => 'Tenant 1',
        'slug' => 'tenant-1',
    ]);

    $tenant2 = Tenant::create([
        'name' => 'Tenant 2',
        'slug' => 'tenant-2',
    ]);

    Alcove::setTenant($tenant1);

    Alcove::run($tenant2, function () {
        // Inside tenant 2 context
    });

    expect(Alcove::tenant())->toBe($tenant1);
});

it('can run without tenant context', function () {
    $tenant = Tenant::create([
        'name' => 'Acme Corp',
        'slug' => 'acme-corp',
    ]);

    Alcove::setTenant($tenant);

    Alcove::runWithoutTenant(function () {
        expect(Alcove::hasTenant())->toBeFalse();
    });

    expect(Alcove::tenant())->toBe($tenant);
});

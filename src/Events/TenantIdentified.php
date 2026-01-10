<?php

declare(strict_types=1);

namespace Alcove\Events;

use Alcove\Contracts\Tenant;
use Illuminate\Foundation\Events\Dispatchable;

class TenantIdentified
{
    use Dispatchable;

    public function __construct(
        public readonly Tenant $tenant,
    ) {}
}

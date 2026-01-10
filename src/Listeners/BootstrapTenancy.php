<?php

declare(strict_types=1);

namespace Alcove\Listeners;

use Alcove\Database\TenantDatabaseManager;
use Alcove\Events\TenantChanged;
use Alcove\Events\TenantIdentified;

class BootstrapTenancy
{
    public function __construct(
        protected TenantDatabaseManager $databaseManager,
    ) {}

    /**
     * Handle tenant identified event.
     */
    public function handleTenantIdentified(TenantIdentified $event): void
    {
        $this->databaseManager->switchToTenant($event->tenant);
    }

    /**
     * Handle tenant changed event.
     */
    public function handleTenantChanged(TenantChanged $event): void
    {
        if ($event->tenant !== null) {
            $this->databaseManager->switchToTenant($event->tenant);
        } else {
            $this->databaseManager->switchToCentral();
        }
    }

    /**
     * Register the listeners for the subscriber.
     *
     * @return array<string, string>
     */
    public function subscribe(): array
    {
        return [
            TenantIdentified::class => 'handleTenantIdentified',
            TenantChanged::class => 'handleTenantChanged',
        ];
    }
}

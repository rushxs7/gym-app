<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

class AutomationSettings extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->add('automation.days_after_expiration_before_fine', 7);
        $this->migrator->add('automation.opening_time', 7);
        $this->migrator->add('automation.closing_time', 22);
    }
}

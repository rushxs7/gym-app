<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

class PaymentSettings extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->add('payments.signup_fee', 275);
        $this->migrator->add('payments.prolongation_fee', 225);
        $this->migrator->add('payments.penalty_fee', 100);
        $this->migrator->add('payments.daytraining_fee', 40);
    }
}

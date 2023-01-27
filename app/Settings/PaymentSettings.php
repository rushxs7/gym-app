<?php

namespace app\Settings;

use Spatie\LaravelSettings\Settings;

class PaymentSettings extends Settings
{
    // Fee for when a new member signs up.
    public float $signup_fee;

    // Fee for when a member prolongates his/her membership.
    public float $prolongation_fee;

    // Penalty fee for when a member fails to prolong his/her membership on time.
    public float $penalty_fee;

    // Fee for when a non-member visits the gym.
    public float $daytraining_fee;

    public static function group(): string
    {
        return 'payments';
    }
}

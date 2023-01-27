<?php

namespace app\Settings;

use Spatie\LaravelSettings\Settings;

class AutomationSettings extends Settings
{
    // days after expiration where a member will be fined upon prolongation
    public int $days_after_expiration_before_fine;

    // the time the gym opens.
    public int $opening_time;

    // the time the gym closes.
    public int $closing_time;

    public static function group(): string
    {
        return 'automation';
    }
}

<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Payment Monitoring
    |--------------------------------------------------------------------------
    |
    | grace_period_days: how many days after date_of_violation a citation's
    | due_date is set to. Replaces the previously hardcoded 72-hour overdue
    | window (72h ≈ 3 days) so it can be tuned without a code change.
    |
    */
    'payment' => [
        'grace_period_days' => (int) env('TVIRS_GRACE_PERIOD_DAYS', 3),
    ],

];

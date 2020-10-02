<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Redis throttle configuration for sending mails through SendMail class
    |--------------------------------------------------------------------------
    |
    | We're doing this so we can have different redis configuration for staging
    | and production, since we're using two different mailhosts, which can't process
    | same amount of emails per second. Default values:
    | redis_send_max_emails for production = 150
    | redis_send_max_emails for staging = 2
    | redis_send_emails_every_n_seconds for production = 3600
    | redis_send_emails_every_n_seconds for staging = 11
    | redis_release_failed_emails_back_to_queue_delay for production = 10
    | redis_release_failed_emails_back_to_queue_delay for staging = 11
    |
    */
    'send_max_emails' => env('REDIS_SEND_MAX_EMAILS', 1),
    'send_emails_every_n_seconds' => env('REDIS_SEND_EMAILS_EVERY_N_SECONDS', 11),
    'release_failed_emails_back_to_queue_delay' => env('REDIS_RELEASE_FAILED_EMAILS_BACK_TO_QUEUE_DELAY', 10),

    /*
    |--------------------------------------------------------------------------
    | Specify specific queue on which the job runs
    |--------------------------------------------------------------------------
    |
    */

    'job_queue' => env('SEND_EMAILS_QUEUE', 'default')
];

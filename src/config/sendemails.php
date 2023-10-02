<?php

return [

    /*
    |-----------------------------------------------------------------------------------
    | Redis throttle configuration for sending mails through SendMail class
    |-----------------------------------------------------------------------------------
    |
    | Here you can configure how many emails you want to send in n seconds. If the job
    | fails, you can set the time in seconds in which will release this failed job
    | back to the queue.
    | Note: The default values are set for Mailtrap SMTP, so you don't get the
    | "Swift_TransportException" exception for too many emails per second.
    |
    */

    'send_max_emails' => env('REDIS_SEND_MAX_EMAILS', 1),
    'send_emails_every_n_seconds' => env('REDIS_SEND_EMAILS_EVERY_N_SECONDS', 11),
    'release_failed_emails_back_to_queue_delay' => env('REDIS_RELEASE_FAILED_EMAILS_BACK_TO_QUEUE_DELAY', 10),

    /*
    |-----------------------------------------------------------------------------------
    | Specify specific queue on which the job runs
    |-----------------------------------------------------------------------------------
    |
    | If you're using different queue form the Redid default one, you can specify
    | your custom queue name here.
    */

    'job_queue' => env('SEND_EMAILS_QUEUE', 'default'),

    /*
    |-----------------------------------------------------------------------------------
    | Define job timeout value
    |-----------------------------------------------------------------------------------
    |
    | Here you can define the maximum number of seconds a job should be allowed
    | to run on the job class itself. Default value is zero, which means the
    | job will run until it's finished completely, no matter how much it takes
    */
    
    'job_timeout_value' => env('JOB_TIMEOUT_VALUE', 0)
];

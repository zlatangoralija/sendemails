<?php

Route::get('/getFailedEmails', 'App\Repositories\Omnitask\SendEmailRepository\SendEmailRepository@getFailedEmails');
Route::get('/resendEmail/{model_id}/{failed_email_id?}', 'App\Repositories\Omnitask\SendEmailRepository\SendEmailRepository@resendEmail');

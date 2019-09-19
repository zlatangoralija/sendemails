<?php

Route::get('/getFailedEmails', 'Omnitask\SendEmailRepository\SendEmailRepository@getFailedEmails');
Route::get('/resendEmail/{model_id}/{failed_email_id?}', 'Omnitask\SendEmailRepository\SendEmailRepository@resendEmail');

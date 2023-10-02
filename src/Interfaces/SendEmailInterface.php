<?php

namespace Omnitask\SendEmailRepository\Interfaces;

use Illuminate\Http\Request;

interface SendEmailInterface
{
    public function sendEmail($recievingUsers, $mailableClass, $model);
    public function sendEmailByMailable($users, $mailable, $model);
    public function validateDataAndSendEmail($users, $mailable, $mailableModel = null);
    public function validateEmail($user);
    public function validateMailableModel($mailableModel);
    public function getFailedEmails(Request $request);
    public function resendEmail(Request $request);
}

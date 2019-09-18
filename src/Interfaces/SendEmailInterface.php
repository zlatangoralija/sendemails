<?php

namespace Omnitask\SendEmailRepository\Interfaces;

use Illuminate\Http\Request;

interface SendEmailInterface
{
    public function sendEmail($recievingUsers, $mailableClass, $model);

    public function getFailedEmails(Request $request);

    public function resendEmail(Request $request);
}
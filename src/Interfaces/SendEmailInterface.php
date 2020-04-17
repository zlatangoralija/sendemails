<?php

namespace Omnitask\SendEmailRepository\Interfaces;

interface SendEmailInterface
{
    public static function getAllFailedEmails();

    public static function getFailedEmailsByModelName($modelName);

    public static function getFailedEmailByModelId($modelName, $modelId);

    public static function getMailableClassName($mailable);

    public function buildModelInstance($modelName, $modelId);

    public function sendEmailByMailable($users, $mailable, $model);

    public function validateDataAndSendEmail($users, $mailable, $mailableModel = null, $failedEmailId = null);

    public function validateEmail($user);

    public function validateMailableModel($mailableModel);

    public function initiateSendEmailJob($users, $mailable, $mailableModel, $mailableModelId, $failedEmailId = null);

    public function resendEmailByMailable($failedEmails);

    public function rebuildAndResendMailable($failedEmail);
}

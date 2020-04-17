<?php

namespace Omnitask\SendEmailRepository;

use App\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Mail\Mailable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Validator;
use Omnitask\SendEmailRepository\Interfaces\SendEmailInterface;
use Omnitask\SendEmailRepository\Jobs\SendEmail;
use Webpatser\Uuid\Uuid;

class SendEmailRepository implements SendEmailInterface
{
    /**
     * Get all failed emails.
     *
     * @return ResendEmail
     */
    public static function getAllFailedEmails(){
        $failedEmails = ResendEmail::get();
        return $failedEmails;
    }

    /**
     * Get all failed emails with matching model name.
     *
     * @param $modelName string
     * @return ResendEmail
     */
    public static function getFailedEmailsByModelName($modelName){
        $failedEmails = ResendEmail::where('model_name', $modelName)->get();
        return $failedEmails;
    }

    /**
     * Get all failed email with matching model name and id.
     *
     * @param $modelName string
     * @param $modelId integer
     * @return ResendEmail
     */
    public static function getFailedEmailByModelId($modelName, $modelId){
        $failedEmail = ResendEmail::where('model_name', $modelName)->where('model_id', $modelId)->first();
        return $failedEmail;
    }

    /**
     * Get Mailable class name as a string.
     *
     * @param $mailable Mailable
     * @return string
     */
    public static function getMailableClassName($mailable){
        $mailableClassName = get_class(unserialize($mailable));
        return $mailableClassName;
    }

    /**
     * Build model instance based on model name and id from failed email table, so we can initiate it
     * and use it in the resendEmailByMailable() function.
     *
     * @param $modelName string
     * @param $modelId int
     * @return Model
     */
    public function buildModelInstance($modelName, $modelId){
        $modelInstnce = $modelName::where('id', $modelId)->first();
        return $modelInstnce;
    }

    /**
     * Send email by already built mailable class. Function checks if $users variable is a collection or single object instance.
     * If variable is collection, we loop through it and send Mailable to each users, else we send it to a single user.
     *
     * @param $users User
     * @param $mailable Mailable
     * @param $mailableModel Model
     */
    public function sendEmailByMailable($users, $mailable, $mailableModel = null)
    {
        if($users){
            if($users instanceof Collection) {
                foreach ($users as $user){
                    $this->validateDataAndSendEmail($user, $mailable, $mailableModel);
                }
            }else{
                $this->validateDataAndSendEmail($users, $mailable, $mailableModel);
            }
        }
    }

    /**
     * Validation of parameters that are being sent to SendEmail job:
     * 1. Users email validation
     * 2. Mailable model validation
     *
     * @param $users User
     * @param $mailable Mailable
     * @param $mailableModel Model
     */

    public function validateDataAndSendEmail($users, $mailable, $mailableModel = null){
        $this->validateEmail($users);
        $mailableModelId = $this->validateMailableModel($mailableModel);
        $this->initiateSendEmailJob($users, $mailable, $mailableModel, $mailableModelId);
    }

    /**
     * Validation of users email.
     *
     * @param $users User
     */

    public function validateEmail($user){
        $validator = Validator::make(['email' => $user->email],
            [ 'email' => 'email']);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }
    }

    /**
     * Validation of Object that is being sent through Mailable Class. We do this in case SendEmailJob fails,
     * so we can save the Model id.
     *
     * @param $mailableModel Model
     */

    public function validateMailableModel($mailableModel){
        if($mailableModel && isset($mailableModel->id)){
            $mailableModelId = $mailableModel->id;
        }else{
            $mailableModelId  = null;
        }

        return $mailableModelId;
    }

    /**
     * Initate SendEmail job that sents out all the emails and handles the redis logic.
     *
     * @param $users User
     * @param $mailable Mailable
     * @param $mailableModel Model
     * @param $mailableModelId integer
     * @param $failedEmailId integer
     */
    public function initiateSendEmailJob($users, $mailable, $mailableModel, $mailableModelId, $failedEmailId = null){
        dispatch(new SendEmail($users, $mailable, $mailableModel, $mailableModelId, $failedEmailId));
    }

    /**
     * Resend previously failed emails. Function checks if $failedEmails variable is a collection or single object instance.
     * If variable is collection, we loop through it and rebuild mailable and model classes in order to try to send them
     * again.
     *
     * @param $failedEmails ResendEmail
     */
    public function resendEmailByMailable($failedEmails){

        if($failedEmails){
            if($failedEmails instanceof Collection) {
                foreach ($failedEmails as $failedEmail){
                    $this->rebuildAndResendMailable($failedEmail);
                }
            }else{
                $this->rebuildAndResendMailable($failedEmails);
            }
        }
    }

    /**
     * Function that initiates the user that email is being sent to, as well as rebuilds mailable and model classes. After that,
     * the whole process of sending emails is re-initiated.
     *
     * @param $failedEmail ResendEmail
     */
    public function rebuildAndResendMailable($failedEmail){
        $user = User::where('id', $failedEmail->user_id)->first();
        $mailableModel = $failedEmail->model_name ? $this->buildModelInstance($failedEmail->model_name, $failedEmail->model_id) : null;
        $mailable = unserialize($failedEmail->mailable_class);
        $this->validateDataAndSendEmail($user, $mailable, $mailableModel);
    }
}

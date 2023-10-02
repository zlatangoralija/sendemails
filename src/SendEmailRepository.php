<?php

namespace Omnitask\SendEmailRepository;

use App\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Mail\Mailable;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Validator;
use Omnitask\SendEmailRepository\Interfaces\SendEmailInterface;
use Omnitask\SendEmailRepository\Jobs\SendEmail;

class SendEmailRepository implements SendEmailInterface
{
    /**
     * Get all failed emails, with matching model_id
     *
     * @param $request Request
     * @return Paginator
     */
    public function getFailedEmails(Request $request){
        $failedEmails = ResendEmail::where('model_id', $request->input('model_id'))->with('user');
        return $failedEmails->paginate(20);
    }

    /**
     * Send email through SendEmail job.
     *
     * @param $recievingUsers Collection
     * @param $mailableClass string
     * @param $model Model
     * @return Redirect
     */

    public function sendEmail($recievingUsers, $mailableClass, $model = null)
    {
        foreach ($recievingUsers as $user){
            $validator = Validator::make(['email' => $user->email],
                [ 'email' => 'email']);

            if ($validator->fails()) {
                return redirect()->back()
                    ->withErrors($validator)
                    ->withInput();
            }

            if($model && isset($model->id)){
                $modelId = $model->id;
            }else{
                $modelId  = 'undefined';
            }

            $mailable = new $mailableClass($user, $model);
            dispatch(new SendEmail($user, $mailable->subject, $mailable, $model, $modelId));
        }
    }


    /**
     * Send email by already built mailable class. Function checks if $users variable is a collection or single object instance.
     * If variable is collection, we loop throuhg it and send Mailable to each users, else we send it to a single user.
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
     * Resend failed emails. Two options:
     * 1. Resend email only for selected user,
     * 2. Resend emails to all users with matching model_id.
     *
     * @param $request Request
     * @return Redirect
     */

    public function resendEmail(Request $request)
    {
        if($request->filled('failed_email_id')) {
            //Send email to single user
            $failedEmails = ResendEmail::where('id', $request->input('failed_email_id'))->get();
        }else{
            //Send emails to all users with matching model_id
            $failedEmails = ResendEmail::where('model_id', $request->model_id)->get();
        }

        foreach ($failedEmails as $failedEmail){
            $recievingUsers = User::where('id', $failedEmail->user_id)->get();
            $mailableClass = $failedEmail->mailable_class;
            $model = $failedEmail->model_name;

            $this->sendEmail($recievingUsers, $mailableClass, unserialize($model));
        }
        return redirect()->back()->with('success', 'Pokušaj ponovnog slanja e-maila.');
    }

    /**
     * Validation of parameters that are being sent to SendEmail job:
     *
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
            $mailableModelId  = 'undefined';
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
     */

    public function initiateSendEmailJob($users, $mailable, $mailableModel, $mailableModelId ){
        dispatch(new SendEmail($users, $mailable, $mailableModel, $mailableModelId))->onQueue(config("sendemails.job_queue"));
    }
}

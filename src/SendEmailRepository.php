<?php

namespace Omnitask\SendEmailRepository;

use App\Jobs\SendEmail;
use App\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Validator;
use Omnitask\SendEmailRepository\Interfaces\SendEmailInterface;

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
        //prepare mailable
        foreach ($recievingUsers as $user){
            //Check if email is valid
            $validator = Validator::make(['email' => $user->email],
                [ 'email' => 'email']);

            if ($validator->fails()) {
                return redirect()->back()
                    ->withErrors($validator)
                    ->withInput();
            }


            //izrendaj mailable (provjeriti da li je model klasa i da li ima id)
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
        }
        else{
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
}
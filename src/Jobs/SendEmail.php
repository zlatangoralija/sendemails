<?php

namespace Omnitask\SendEmailRepository\Jobs;
use Log;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Redis;
use App\User;
use Illuminate\Mail\Mailable;
use Omnitask\SendEmailRepository\ResendEmail;

class SendEmail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $user;
    protected $subject;
    protected $mailable;
    protected $modelClassName;
    protected $modelId;

    /**
     * Create a new job instance.
     *
     * @param $user User
     * @param $subject string
     * @param $mailable Mailable
     * @param $modelClassName string
     * @param $modelId integer
     */
    public function __construct($user, $subject, $mailable, $modelClassName, $modelId)
    {
        $this->user = $user;
        $this->subject = $subject;
        $this->mailable = $mailable;
        $this->modelClassName = $modelClassName;
        $this->modelId = $modelId;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        try{
          Redis::throttle('SendEmail')
            ->allow(config("sendemails.send_max_emails"))
                ->every(config("sendemails.send_emails_every_n_seconds"))
                ->then(function () {
                    Mail::to($this->user)->send($this->mailable);
                    if($this->user->id){
                        ResendEmail::where('user_id', $this->user->id)->delete();
                    }
                    Log::info('SendEmail  email: '.$this->user->email);
                }, function () {
                    return $this->release(config("sendemails.release_failed_emails_back_to_queue_delay"));
                });
        }catch (\Exception $e){
            ResendEmail::updateOrCreate([
                'id' => $this->modelId
            ],[
                'user_id' => $this->user->id, //id usera
                'mailable_class' => get_class($this->mailable), //Email klasa
                'model_id' => $this->modelId, //Id emaila iz modela
                'model_name' => serialize($this->modelClassName), //naziv modela klase
                'exception' => $e->getMessage(),
            ]);
            Log::info($e->getMessage());
        }
    }
}


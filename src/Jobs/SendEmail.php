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
    protected $mailable;
    protected $modelClassName;
    protected $modelId;
    protected $failedEmailId;

    /**
     * Create a new job instance.
     *
     * @param $user User
     * @param $mailable Mailable
     * @param $modelClassName string
     * @param $modelId integer
     * @param $failedEmailId integer
     */
    public function __construct($user, $mailable, $modelClassName = null, $modelId = null, $failedEmailId = null)
    {
        $this->user = $user;
        $this->mailable = $mailable;
        $this->modelClassName = $modelClassName;
        $this->modelId = $modelId;
        $this->failedEmailId = $failedEmailId;
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
                    if($this->failedEmailId){
                        ResendEmail::where('id', $this->failedEmailId)->delete();
                    }
                    Log::info('Omnitask\SendEmail sending email to: '.$this->user->email);
                }, function () {
                    return $this->release(config("sendemails.release_failed_emails_back_to_queue_delay"));
                });
        }catch (\Exception $e){
            ResendEmail::updateOrCreate([
                'id' => $this->failedEmailId
            ],[
                'user_id' => $this->user->id,
                'mailable_class' => serialize($this->mailable),
                'model_id' => $this->modelId ?: null,
                'model_name' => $this->modelClassName ? get_class($this->modelClassName) : null,
                'exception' => $e->getMessage(),
            ]);
            Log::info('Omnitask\SendEmail failed to send email. Exception: ' . $e->getMessage());
        }
    }
}


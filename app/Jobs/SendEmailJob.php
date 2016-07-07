<?php

namespace App\Jobs;

use App\Jobs\Job;
use App\Mailers\AppMailer;
use Illuminate\Contracts\Bus\SelfHandling;
use Illuminate\Contracts\Queue\ShouldQueue;
use Mail;

class SendEmailJob extends Job implements SelfHandling, ShouldQueue
{
	protected $functionName;
	protected $data;
    protected $noReply;
    protected $baseUrl;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($functionName, $data)
    {
        $this->functionName = $functionName;
        $this->data = $data;

    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $functionName = $this->functionName;
        $mailer = new AppMailer();
        $mailer->$functionName($this->data);
    }
}
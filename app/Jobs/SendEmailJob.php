<?php

namespace App\Jobs;

use App\Jobs\Job;
use Illuminate\Contracts\Bus\SelfHandling;
use Mail;

class SendEmailJob extends Job implements SelfHandling
{
	protected $email_function;
	protected $type;
	
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($function,$type)
    {
        $this->email_function = $function;
		$this->type = $type;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
		call_user_func($this->email_function);
    }
}

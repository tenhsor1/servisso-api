<?php

namespace App\Jobs;

use App\Jobs\Job;
use Illuminate\Contracts\Bus\SelfHandling;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\SerializesModels;
use SuperClosure\Serializer;
use Mail;

class SendEmailJob extends Job implements SelfHandling, ShouldQueue
{
	use InteractsWithQueue,SerializesModels;
	
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
		$serializer = new Serializer();
		$unserialized = $serializer->unserialize($this->email_function);
		call_user_func($unserialized);*/
    }
}

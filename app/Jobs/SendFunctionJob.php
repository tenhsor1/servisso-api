<?php

namespace App\Jobs;

use App\Jobs\Job;
use Illuminate\Contracts\Bus\SelfHandling;
use Illuminate\Contracts\Queue\ShouldQueue;
use Mail;

class SendTaskJob extends Job implements SelfHandling, ShouldQueue
{
    protected $general_function;
    protected $type;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($function,$type)
    {
        $this->general_function = $function;
        $this->type = $type;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        call_user_func($this->general_function);
    }
}

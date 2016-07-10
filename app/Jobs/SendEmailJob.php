<?php
namespace App\Jobs;
use App\Jobs\Job;
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
        \Log::debug('cooooooons');
        $this->functionName = $functionName;
        $this->data = $data;
        $this->noReply = \Config::get('mail.from_no_reply');
        $this->baseUrl = \Config::get('app.front_url');
    }
    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $functionName = $this->functionName;
		$this->$functionName();
    }
    public function sendVerificationEmail(){
        $data = $this->data;
        Mail::send('emails.verify', ['code' => $this->data['token'], 'baseUrl' => $this->baseUrl], function ($m) use ($data){
            $m->from($this->noReply['address'], $this->noReply['name'])
                ->to($data['email'], $data['name'])
                ->subject('Verifica tu e-mail para continuar');
        });
    }
    public function sendNonRegisteredBranchEmail(){
        $data = $this->data;
		
        Mail::send('emails.non-registered-branch', $data, function ($m) use ($data){
            $m->from($this->noReply['address'], $this->noReply['name'])
                ->to($data['branch_email'], $data['branch_name'])
                ->subject('Alguien requiere de tus servicios!');
        });
    }
    public function sendRegisteredBranchEmail(){
        $data = $this->data;
        Mail::send('emails.registered-branch', $data, function ($m) use ($data){
            $m->from($this->noReply['address'], $this->noReply['name'])
                ->to($data['user_email'], $data['branch_name'])
                ->subject('Alguien requiere de tus servicios!');
        });
    }
}
<?php
namespace App\Mailers;
use Mail;
use App\Jobs\SendEmailJob;

/**
* Wrapper of distinct functions for sending emails
*
*/
class AppMailer
{

	use \Illuminate\Foundation\Bus\DispatchesJobs;

    function __construct()
    {
        $this->noReply = \Config::get('mail.from_no_reply');
        $this->baseUrl = \Config::get('app.front_url');
    }

    public function pushToQueue($function, $data){
        $job = (new SendEmailJob($function,$data))->onQueue('emails');
        $this->dispatch($job);
    }

    public function sendVerificationEmail($data){

        Mail::send('emails.verify', ['code' => $data['token'], 'baseUrl' => $this->baseUrl], function ($m) use ($data){
            $m->from($this->noReply['address'], $this->noReply['name'])
                ->to($data['email'], $data['name'])
                ->subject('Verifica tu e-mail para continuar');
        });
    }

	/**
	* Método para mandar un email cuando una branch no registrada(inegi) recibe
	* una solicitud de cotización
	*/
	public function sendNonRegisteredBranchEmail($data){

		Mail::send('emails.non-registered-branch', $data, function ($m) use ($data){
            $m->from($this->noReply['address'], $this->noReply['name'])
                ->to($data['branch_email'], $data['branch_name'])
                ->subject('Alguien requiere de tus servicios!');
        });
    }

	/**
	* Método para mandar un email cuando una branch registrada(no inegi) recibe
	* una solicitud de cotización
	*/
	public function sendRegisteredBranchEmail($data){

		Mail::send('emails.registered-branch', $data, function ($m) use ($data){
            $m->from($this->noReply['address'], $this->noReply['name'])
                ->to($data['user_email'], $data['branch_name'])
                ->subject('Alguien requiere de tus servicios!');
        });
    }

    /**
     * Send an email with a new task that has been added that could be of interest for the branch owner
     * @param  [array] $data Data needed by the email that will be sent to the branch owner
     */
    public function sendNewTaskEmail($data){
        Mail::send('emails.new-task-branch', $data, function ($m) use ($data){
            $m->from($this->noReply['address'], $this->noReply['name'])
                ->to($data['branch_email'], $data['branch_name'])
                ->subject('Hay un trabajo para '. $data['category']);
        });
    }
}
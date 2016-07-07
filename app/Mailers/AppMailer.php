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
        $this->no_reply = \Config::get('mail.from_no_reply');
        $this->baseUrl = \Config::get('app.front_url');
    }
/*    public function sendVerificationEmail($user){
        $function = function() use ($user){
            Mail::send('emails.verify', ['code' => $user->token, 'baseUrl' => $this->baseUrl], function ($m) use ($user){
                $m->from($this->no_reply['address'], $this->no_reply['name'])
                    ->to($user->email, $user->name)
                    ->subject('Verifica tu e-mail para continuar');
            });
        };
        $job = (new SendEmailJob($function,'user-verification-email'))->onQueue('emails');
        $this->dispatch($job);
    }
*/
    public function sendVerificationEmail($user){
		/*$function = function() use ($user){
			Mail::send('emails.verify', ['code' => $user->token, 'baseUrl' => $this->baseUrl], function ($m) use ($user){
				$m->from($this->no_reply['address'], $this->no_reply['name'])
					->to($user->email, $user->name)
					->subject('Verifica tu e-mail para continuar');
			});
		};*/
        $data = [
            'token' => $user->token,
            'email' => $user->email,
            'name'  => $user->name,
        ];
		$job = (new SendEmailJob('sendVerificationEmail',$data))->onQueue('emails');
		$this->dispatch($job);
    }
	/**
	* Método para mandar un email cuando una branch no registrada(inegi) recibe
	* una solicitud de cotización
	*/
	public function sendNonRegisteredBranchEmail($data){
		$job = (new SendEmailJob('sendNonRegisteredBranchEmail',$data))->onQueue('emails');
		$this->dispatch($job);
    }
	/**
	* Método para mandar un email cuando una branch registrada(no inegi) recibe
	* una solicitud de cotización
	*/
	public function sendRegisteredBranchEmail($data){
		$job = (new SendEmailJob('sendRegisteredBranchEmail',$data))->onQueue('emails');
		$this->dispatch($job);
    }
    /**
     * Send an email with a new task that has been added that could be of interest for the branch owner
     * @param  [array] $data Data needed by the email that will be sent to the branch owner
     */
    public function sendNewTaskEmail($data){
        /*
        $function = function() use ($data){
            Mail::send('emails.new-task-branch', $data, function ($m) use ($data){
                $m->from($this->no_reply['address'], $this->no_reply['name'])
                    ->to($data['user_email'], $data['branch_name'])
                    ->subject('Hay una nueva tarea que te podría interesar!');
            });
        };
        $job = (new SendEmailJob($function,'task-branch-email'))->onQueue('emails');
        $this->dispatch($job);
        */
    }
}
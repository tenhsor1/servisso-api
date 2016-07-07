<?php
namespace App\Mailers;
use Mail;
use App\Jobs\SendEmailJob;
use SuperClosure\Serializer;

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

    public function sendVerificationEmail($user){
		
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
	
	/**
	* Método para mandar un email cuando una branch no registrada(inegi) recibe
	* una solicitud de cotización
	*/
	public function sendNonRegisteredBranchEmail($data){
		
		$function = function() use ($data){
			Mail::send('emails.non-registered-branch', $data, function ($m) use ($data){
				$m->from($this->no_reply['address'], $this->no_reply['name'])
					->to($data['branch_email'], $data['branch_name'])
					->subject('Alguien requiere de tus servicios!');
			});
		};
		
		$serializer = new Serializer();	
		$serialized = $serializer->serialize($function);		
		$job = (new SendEmailJob($serialized,'service-requested-email-inegi'))->onQueue('emails')->delay(15);
		$this->dispatch($job);
    }
	
	/**
	* Método para mandar un email cuando una branch registrada(no inegi) recibe
	* una solicitud de cotización
	*/
	public function sendRegisteredBranchEmail($data){
		
		$function = function() use ($data){		
			Mail::send('emails.registered-branch', $data, function ($m) use ($data){
				$m->from($this->no_reply['address'], $this->no_reply['name'])
					->to($data['user_email'], $data['branch_name'])
					->subject('Alguien requiere de tus servicios!');
			});
		};

		$job = (new SendEmailJob($function,'service-requested-email'))->onQueue('emails');
		$this->dispatch($job);
    }
}
<?php
namespace App\Mailers;
use Mail;
/**
* Wrapper of distinct functions for sending emails
*
*/
class AppMailer
{

    function __construct()
    {
        $this->no_reply = \Config::get('mail.from_no_reply');
        $this->baseUrl = \Config::get('app.front_url');
    }

    public function sendVerificationEmail($user){
        Mail::send('emails.verify', ['code' => $user->token, 'baseUrl' => $this->baseUrl], function ($m) use ($user){
            $m->from($this->no_reply['address'], $this->no_reply['name'])
                ->to($user->email, $user->name)
                ->subject('Verifica tu e-mail para continuar');
        });
    }
	
	/**
	* Método para mandar un email para cuando una branch no registrada(inegi) recibe
	* una solicitud de cotización
	*/
	public function sendNonRegisteredBranchEmail($data){
        Mail::send('emails.non-registered-branch', $data, function ($m) use ($data){
            $m->from($this->no_reply['address'], $this->no_reply['name'])
                ->to($data['branch_email'], $data['branch_name'])
                ->subject('Alguien require de tus servicios!');
        });
    }
	
	/**
	* Método para mandar un email para cuando una branch registrada(no inegi) recibe
	* una solicitud de cotización
	*/
	public function sendRegisteredBranchEmail($data){
        Mail::send('emails.registered-branch', $data, function ($m) use ($data){
            $m->from($this->no_reply['address'], $this->no_reply['name'])
                ->to($data['branch_email'], $data['branch_name'])
                ->subject('Alguien require de tus servicios!');
        });
    }
}
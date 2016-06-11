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
	
	public function sendNonRegisteredBranchEmail($user){
        Mail::send('emails.non-registered-branch', ['code' => 'code', 'baseUrl' => '#'], function ($m) use ($user){
            $m->from($this->no_reply['address'], $this->no_reply['name'])
                ->to('ernesto.soft45@gmail.com', 'Ernesto Hdez Noriega')
                ->subject('Alguien require de tus servicios!');
        });
    }
}
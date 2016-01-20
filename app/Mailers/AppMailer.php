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
        $this->emails = \Config::get('app.emails');
        $this->email_names = \Config::get('app.email_names');
    }

    public function sendVerificationEmail($user){
        Mail::send('emails.verify', ['code' => $user->token], function ($m) use ($user){
            $m->from($this->emails['NOREPLY'], $this->email_names['NOREPLY'])
                ->to($user->email, $user->name)
                ->subject('Verifica tu e-mail para continuar');
        });
    }
}
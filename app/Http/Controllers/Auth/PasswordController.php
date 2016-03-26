<?php

namespace App\Http\Controllers\Auth;

use Illuminate\Http\Request;
use Illuminate\Mail\Message;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Password;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\ResetsPasswords;

class PasswordController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Password Reset Controller
    |--------------------------------------------------------------------------
    |
    | This controller is responsible for handling password reset requests
    | and uses a simple trait to include this behavior. You're free to
    | explore this trait and override any methods you wish to tweak.
    |
    */

    use ResetsPasswords;

    /**
     * Create a new password controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->emails = \Config::get('app.emails');
        $this->email_names = \Config::get('app.email_names');
        $this->subject = trans('passwords.reset.subject');
        $this->middleware('guest');
    }

    /**
     * Send a reset link to the given user.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function postEmail(Request $request)
    {
        $this->validate($request, ['email' => 'required|email']);

        $response = Password::sendResetLink($request->only('email'), function (Message $message) {
            //$message->from($this->emails['NOREPLY'], $this->email_names['NOREPLY']);
            $message->subject($this->getEmailSubject());
        });

        switch ($response) {
            case Password::RESET_LINK_SENT:
                $resp = ['code' => 200,
                        'data' => $request->only('email'),
                        'message' => trans('passwords.reset.sent')];
                return response()->json($resp,200);

            case Password::INVALID_USER:
                $resp = ['code' => 400,
                        'data' => $request->only('email'),
                        'message' => trans('passwords.reset.user_invalid')];
                return response()->json($resp,400);
        }
    }
}

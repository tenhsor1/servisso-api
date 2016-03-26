<?php
namespace App\Http\Controllers\Auth;

use Illuminate\Http\Request;
use Illuminate\Mail\Message;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Password;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\ResetsPasswords;

use JWTAuth;
use App\User;
use Validator;

class PassController extends Controller{
    use ResetsPasswords;

    public function postEmail(Request $request)
    {
        $rules = ['email' => 'required|email'];
        $v = Validator::make($request->all(),$rules);

        if($v->fails()){
            $response = ['error' => 'Bad Request', 'data' => $v->messages(),'code' => 422];
            return response()->json($response,422);
        }

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

    public function postReset(Request $request)
    {
        $rules = [
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|confirmed|min:8',
        ];
        $v = Validator::make($request->all(),$rules);

        if($v->fails()){
            $response = ['error' => 'Bad Request', 'data' => $v->messages(),'code' => 422];
            return response()->json($response,422);
        }

        $credentials = $request->only(
            'email', 'password', 'password_confirmation', 'token'
        );

        $response = Password::reset($credentials, function ($user, $password) {
            $this->resetPassword($user, $password);
        });

        switch ($response) {
            case Password::PASSWORD_RESET:
                $user = User::byEmail($request)->first();
                \Log::debug(json_encode($user));

                $token = JWTAuth::fromUser($user, ['role'=>'USER']);
                $user->access = $token;
                $resp = ['code' => 200,
                        'data' => $user,
                        'message' => trans('passwords.reset.success')];
                return response()->json($resp,200);
            default:
                $resp = ['code' => 400,
                        'data' => $request->only('email'),
                        'message' => trans('passwords.reset.token_invalid')];
                return response()->json($resp,400);
        }
    }
}
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
use Carbon\Carbon;
use DB;

class PassController extends Controller{
    use ResetsPasswords;

    /**
     * Create a new password controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest');
        $this->subject = trans('passwords.reset.subject');
    }

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
                        'error' => trans('passwords.reset.user_invalid')];
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
                        'error' => trans('passwords.reset.token_invalid')];
                return response()->json($resp,400);
        }
    }

    function checkToken(Request $request, $token){
        // I want to add some filter here like
        // if( token_is_expired($token) ) return false;
        $expireLimit = \Config::get('auth.password.expire');
        $tokenDB = DB::table('password_resets')
            ->where('token','=',$token)
            ->where('created_at','>',Carbon::now()->subMinutes($expireLimit))
            ->first();

        if($tokenDB){
            $response = ['code' => 200,
                        'data' => ['email' => $tokenDB->email],
                        'message' => trans('passwords.reset.token_valid')];
                return response()->json($response,200);
        }
        $response = ['code' => 403,
                        'data' => ['token' => $token],
                        'error' => trans('passwords.reset.token_not_found')];
        return response()->json($response,403);
     }
}
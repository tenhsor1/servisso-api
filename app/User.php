<?php

namespace App;

use Illuminate\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Foundation\Auth\Access\Authorizable;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;
use Validator;
use JWTAuth;

class User extends Model implements AuthenticatableContract,
                                    AuthorizableContract,
                                    CanResetPasswordContract
{
    use Authenticatable, Authorizable, CanResetPassword, SoftDeletes;

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'users';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [   'name'
                            , 'lastname'
                            , 'email'
                            , 'password'
                            , 'phone'
                            , 'address'
                            , 'zipcode'
                            , 'state_id'
                            , 'invitations'
                        ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = ['password', 'deleted_at', 'created_at', 'updated_at','token'];




	 public static function getValidationsPassword(){
        $validation = [
           'password' => 'required|max:99|min:7',
           'passwordNew' => 'required|max:99|min:7',
           'passwordConfirm' => 'required|max:99|min:7',
        ];

        return $validation;
    }

    public static function boot()
    {
        User::creating(function ($user) {
            $userRoles = \Config::get('app.user_roles');
            $tokenArray = ['random' => str_random(16)
                            , 'email' => $user->email
                            , 'role' => $userRoles['USER']];
            $encrypted = \Crypt::encrypt($tokenArray);
            $user->token = $encrypted;
            $user->password = \Hash::make($user->password);
        });
    }

    public function services(){
        return $this->morphMany('App\Service', 'userable');
    }

    public function tasks(){
        return $this->hasMany('App\Task');
    }

    public function notifications(){
        return $this->morphMany('App\Notifications', 'sender');
    }

    public function socials(){
        return $this->hasMany('App\UserSocial');
    }

	public function country()
    {
        // 1 admin can have one country
        return $this->hasOne('App\Country');
    }

	public function state()
    {
        // 1 admin can have one state
        return $this->hasOne('App\State');
    }

	public function invitations(){
		return $this->hasMany('App\UserInvitation');
	}

    public function ownRates()
    {
        // 1 admin can have one state
        return $this->hasMany('App\UserRate');
    }

    public function chatParticipants(){
        return $this->hasMany('App\ChatParticipant');
    }

    public function getBranch($id){
        $branch = Branch::find($id)
            ->with('company')
            ->leftJoin('companies','companies.id','=','branches.company_id')
            ->leftJoin('users','users.id','=','companies.user_id')
            ->where('users.id', $this->id)
            ->where('branches.id', $id)
            ->select('branches.*')
            ->get();
        return $branch;
    }

    public static function validatePayloadStore($request){
        $v = Validator::make($request->all(), User::getRules(), User::getMessages());

        $v->sometimes('captcha', 'required|string', function($input){
            return strlen($input->val) <= 0;
        });

        if($v->fails()){
            $response = json_encode($v->errors());
            abort(400, $response);
            return false;
        }
        return true;
    }

	public static function getRules(){
		$rules = array(
				'email' => ['required','email','unique:users'],
				'password' => ['required','min:8'],
				'name' => ['required','min:3','max:45'],
				'lastname' => ['required','min:3','max:45'],
			);

		return $rules;
	}

    public static function getMessages(){
        $messages =
        [
            'email.required' => 'Email es obligatorio',
            'email.email' => 'Email no válido',
            'email.unique' => 'La cuenta de correo ya fue utilizada',
			'password.required' => 'Contraseña es obligatoria',
            'password.min' => 'Contraseña debe tener minimo :min caracteres',
			'name.required' => 'Nombre es obligatorio',
            'name.min' => 'Nombre debe tener minimo :min caracteres',
            'name.max' => 'Nombre debe tener máximo :max caracteres',
			'lastname.required' => 'Apellido es obligatorio',
            'lastname.min' => 'Apellido debe tener minimo :min caracteres',
            'lastname.max' => 'Apellido debe tener máximo :max caracteres'
        ];

        return $messages;
    }

    public function scopeByEmail($query, $request){
        $email = $request->input('email', null);
        if($email){
            $query->where('email', '=', $email);
        }
        return $query;
    }

    public function loginSocial($values){
        $userSocial = UserSocial::firstOrNew(['user_id' => $this->id, 'platform' => $values['platform']]);
        $userSocial->user_id = $this->id;
        $userSocial->platform = $values['platform'];
        $userSocial->platform_id = $values['platform_id'];
        $userSocial->email = $values['email'];
        $userSocial->name = $values['name'];
        $userSocial->avatar = (array_key_exists('avatar', $values)) ? $values['avatar'] : null;
        $userSocial->token = $values['token'];
        if($userSocial->save()){
            $extraClaims = ['role' => 'USER'];
            $token =  JWTAuth::fromUser($this, $extraClaims);
            return $token;
        }
        return null;
    }
}

/**
 * Used to hide certain fields for the user model,
 * basically for tasks where the branch still doesn´t have permissions for see the user info
 */
class UserHidden extends User
{
    //only show id and name for this kind of model
    protected $visible = ['id', 'name'];
}

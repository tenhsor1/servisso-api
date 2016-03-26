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
                            , 'last_name'
                            , 'email'
                            , 'password'
                            , 'phone'
                            , 'address'
                            , 'zipcode'
                            , 'state_id'
                        ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = ['state_id','password', 'deleted_at', 'created_at', 'updated_at','token'];

    public static function boot()
    {
        User::creating(function ($user) {
            $userRoles = \Config::get('app.user_roles');
            $tokenArray = ['random' => str_random(16)
                            , 'email' => $user->email
                            , 'role' => $userRoles['USER']];
            $encrypted = \Crypt::encrypt($tokenArray);
            $user->token = $encrypted;
        });
    }

    public function services(){
        return $this->morphMany('App\Service', 'userable');
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

    public function setPasswordAttribute($value)
    {
        $this->attributes['password'] = \Hash::make($value);
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

    public static function getMessages(){
        $messages =
        [
            'required' => ':attribute is required',
            'max' => ':attribute length too long',
            'min' => ':attribute length too short',
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
}

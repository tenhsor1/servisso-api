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
use App\Branch;

class Partner extends Model implements AuthenticatableContract,
                                    AuthorizableContract,
                                    CanResetPasswordContract
{

	use SoftDeletes;
    use Authenticatable, Authorizable, CanResetPassword;

    protected $table = 'partners';

	protected $fillable = array('email', 'password', 'name','longitude','lastname',
								'birthdate','phone','address','zipcode','status','state_id','country_id','plan_id');

	//protected $guarded = ['state_id','country_id','plan_id'];

	protected $hidden = ['password','deleted_at','created_at','updated_at'];

    public function companies(){
        //1 partner can have multiple companies
        return $this->hasMany('App\Company');
    }

    public function setPasswordAttribute($value)
    {
        $this->attributes['password'] = \Hash::make($value);
    }

	/**
	* Se obtienen los mensajes de errores
	*/
	public static function getMessages(){
		$messages = [
		'required' => ':attribute is required',
		'email' => ':attribute has invalid format',
		'date' => ':attribute should be 10 digits',
		'mimes' => ':attribute invalid format, allow: jpeg,png,bmp',
		'digits' => ':attribute should be 10 digits',
		'max' => ':attribute length too long',
		'min' => ':attribute length too short',
		'string' => ':attribute should be characters only'
		];

		return $messages;
	}

	/**
	* Se obtienen las validaciones del modelo Partner
	*/
	public static function getValidations(){
		$validation = ['email' => 'required|email|max:70|min:11',
				'password' => 'required|max:99|min:7',
				'name' => 'required|max:45|min:4',
				'lastname' => 'required|max:45|min:4',
				'birthdate' => 'max:20|digits:10',
				'phone' => 'required|max:20|min:10',
				'address' => 'required|max:150|min:10',
				'zipcode' => 'required|max:10|min:4',
				'state_id' => 'required',
				'country_id' => 'required',
				'status' => 'required',
				'plan_id' => 'required'];

		return $validation;
	}

    public function getBranch($id){
        $branch = Branch::find($id)
            ->with('company')
            ->leftJoin('companies','companies.id','=','branches.company_id')
            ->leftJoin('partners','partners.id','=','companies.partner_id')
            ->where('partners.id', $this->id)
            ->where('branches.id', $id)
            ->select('branches.*')
            ->get();
        return $branch;
    }
}

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

	protected $hidden = ['password','deleted_at','created_at','updated_at','role_id','role'];

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
	
	/**
	* Se usa para verificar que una URL tiene un formato(patrón) válido.
	* $url = url que envia el cliente.
	*/
	public static function getUrlPattern($url){
		$patterns = Partner::getPatterns();
		
		//Se relaciona la url con algún patrón
		for($i = 0;$i < count($patterns);$i++){
			if(preg_match($patterns[$i],$url)){
				return $i+1;
			}
		}
		
		//Si la url no se relaciona con ninguno entonces esta mal  el formato, se devuelve cero.
		return 0;
	}
	
	/**
	* Se obtiene un patrón, depiendiendo del número de patrón solicitado.
	* $pattern_number = número de patrón
	* Los patrones comienzan de la posición 1 en adelante. 
	*/
	public static function getPattern($pattern_number){	
		$pattern_number += -1;		
		$patterns = Partner::getPatterns();
		
		//Si el patrón solicitado no existe entonces se regresara el patrón por 'default'(posición cero).
		if($pattern_number > count($patterns))
			$pattern_number = 0;
		
		return $patterns[$pattern_number];
	}
	
	/**
	* Se relaciona la url solicitada con algún patrón.
	* pattern 1: default
	* pattern 2: limit:require, page:optional, orderBy:optional, orderType:optional
	* pattern 3: search:require, fields:optional, orderBy:optional, orderType:optional, limit:optional, page:optional
	* pattern 4: start:require, end:optional, orderBy:optional, orderType:optional, limit:optional, page:optional
	* pattern 5: search:require, fields:optional, start:require, end:optional, dateFields:require, orderBy:optional, orderType:optional,
				 limit:optional, page:optional
	*/
	public static function getPatterns(){
		
		//'$domain' se tiene que cambiar dependiendo del controlador donde estamos	
		$domain = "\/servisso-api\/public\/v1\/partner";
		
		$patterns = array(
			"/^$domain$/",
			"/^$domain(\?limit=[0-9]{1,2})(&page=[1-9]{1,2})?(&orderBy=\([[:alpha:],]+\))?(&orderType=\([[:alpha:],]+\))?$/",
			"/^$domain(\?search=(\w\+?)+)(&fields=\([[:alpha:],]+\))?(&orderBy=\([[:alpha:],]+\))?(&orderType=\([[:alpha:],]+\))?(&limit=[0-9]{1,2})?(&page=[1-9]{1,2})?$/",
			"/^$domain(\?start=20[1-3][0-9]-[0-1][0-9]-[0-3][0-9])(&end=20[1-3][0-9]-[0-1][0-9]-[0-3][0-9])?(&orderBy=\([[:alpha:],]+\))?(&orderType=\([[:alpha:],]+\))?(&limit=[0-9]{1,2})?(&page=[1-9]{1,2})?$/",
			"/^$domain\?search=(\w\+?)+(&fields=\([[:alpha:],]+\))?&start=20[1-3][0-9]-[0-1][0-9]-[0-3][0-9](&end=20[1-3][0-9]-[0-1][0-9]-[0-3][0-9])?&dateFields=\([[:alpha:],]+\)(&orderBy=\([[:alpha:],]+\))?(&orderType=\([[:alpha:],]+\))?(&limit=[0-9]{1,2})?(&page=[1-9]{1,2})?$/"
		);
		
		return $patterns;
	}
	
	public static function getValidFields(){
								
		$fields = array('email','name','lastname','birthdate','phone','address',
		'zipcode','status','state_id','country_id','plan_id');
				
		return $fields;
	}
	
}

<?php

namespace App;

use Illuminate\Auth\Authenticatable;
//use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Foundation\Auth\Access\Authorizable;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;
use App\Branch;
use App\Extensions\ServissoModel;

class Partner extends ServissoModel implements AuthenticatableContract,
										AuthorizableContract,
										CanResetPasswordContract
{

	use SoftDeletes;
    use Authenticatable, Authorizable, CanResetPassword;

    protected $table = 'partners';

	protected $fillable = array('email', 'password', 'name','longitude','lastname',
								'birthdate','phone','address','zipcode','status','state_id','country_id','plan_id');

	//protected $guarded = ['state_id','country_id','plan_id'];

	protected $hidden = ['password','deleted_at','created_at','updated_at','role_id','role', 'token'];

	protected $searchFields = [
        'email',
        'name',
        'lastname',
		'birthdate',
		'phone',
		'address',
		'zipcode'
    ];

    protected $betweenFields = [
        'created',
        'updated',
		'deleted'
    ];

    protected $orderByFields = [
        'created',
        'updated',
		'deleted',
		'email',
		'name',
		'lastname',
		'birthdate',
		'phone',
		'address',
		'zipcode'
    ];

    public static function boot()
    {
        Partner::creating(function ($partner) {
            $userRoles = \Config::get('app.user_roles');
            $tokenArray = ['random' => str_random(16)
                            , 'email' => $partner->email
                            , 'role' => $userRoles['PARTNER']];
            $encrypted = \Crypt::encrypt($tokenArray);
            $partner->token = $encrypted;
        });
    }

    public function companies(){
        //1 partner can have multiple companies
        return $this->hasMany('App\Company');
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
     * Used for search using 'LIKE', based on query parameters passed to the
     * request (example: services?search=test&searchFields=description,company,address)
     * @param  [QueryBuilder] $query    The consecutive query
     * @param  [Request] $request       The HTTP Request object of the call
     * @param  array  $defaultFields    The default fields if there are no 'searchFields' param passed
     * @return [QueryBuilder]           The new query builder
     */
    public function scopeSearchBy($query, $request, $defaultFields=array('name')){
        $fields = $this->searchParametersAreValid($request);
        if($fields){
            $search = $request->input('search');
			$where = "where";
            $searchFields = is_array($fields) ? $fields : $defaultFields;
            foreach ($searchFields as $searchField) {
                switch ($searchField) {
                    case 'email':
                        //search by the email of the service
                        $query->$where('email', 'LIKE', '%'.$search.'%');
                        break;
                    case 'name':
                        //search by the name of the service
                        $query->$where('name', 'LIKE', '%'.$search.'%');
                        break;
                    case 'lastname':
                        //search by the lastname of the service
                        $query->$where('lastname', 'LIKE', '%'.$search.'%');
                        break;
					case 'phone':
						//search by the phone of the service
                        $query->$where('phone', 'LIKE', '%'.$search.'%');
						break;
					case 'address':
						//search by the address of the service
                        $query->$where('address', 'LIKE', '%'.$search.'%');
						break;
					case 'zipcode':
						//search by the zipcode of the service
                        $query->$where('zipcode', 'LIKE', '%'.$search.'%');
						break;
                }
				$where = "orWhere";
            }
        }
        return $query;
    }

	/**
     * Used for search between a end and a start, based on query parameters passed to the
     * request (example: services?start=2015-11-19&end=2015-12-31&betweenFields=updated,created)
     * @param  [QueryBuilder] $query    The consecutive query
     * @param  [Request] $request       The HTTP Request object of the call
     * @param  array  $defaultFields    The default fields if there are no 'betweenFields' param passed
     * @return [QueryBuilder]           The new query builder
     */
    public function scopeBetweenBy($query, $request, $defaultFields=array('created')){
        $fields = $this->betweenParametersAreValid($request);
        if($fields){
        	$start = null;
        	if($request->get('start'))
            	$start = $request->get('start') . " 00:00:00";
            $end = null;
            if($request->get('end'))
            	$end = $request->get('end') . " 23:59:59";
			$where = "where";
            $searchFields = is_array($fields) ? $fields : $defaultFields;
            foreach ($searchFields as $searchField) {
                switch ($searchField) {
                    case 'created':
                        //search depending on the creation time
                        if($start)
                            $query->$where('created_at', '>=', $start);
                        if($end)
                            $query->where('created_at', '<=', $end);
                        break;
                    case 'updated':
                        //search depending on the updated time
                        if($start)
                            $query->$where('updated_at', '>=', $start);
                        if($end)
                            $query->where('updated_at', '<=', $end);
                        break;
					case 'deleted':
                        //search depending on the deleted time
                        if($start)
                            $query->$where('deleted_at', '>=', $start);
                        if($end)
                            $query->where('deleted_at', '<=', $end);
                        break;
                }
				$where = "orWhere";
            }
        }
        return $query;
    }

	/**
     * Used for ordering the result of a get request
     * (example: services?orderBy=created,updated&orderTypes=ASC,DESC)
     * @param  [QueryBuilder] $query    The consecutive query
     * @param  [Request] $request       The HTTP Request object of the call
     * @return [QueryBuilder]           The new query builder
     */
    public function scopeOrderByCustom($query, $request){
        $orderFields = $this->orderByParametersAreValid($request);
        if($orderFields){
			$orderTypes = explode(',', ($request->input('orderType')) ? $request->input('orderType') : 'desc');
            $cont=0;
            foreach ($orderFields as $orderField) {
                $orderType = $orderTypes[$cont] ? $orderTypes[$cont] : 'DESC';
                switch ($orderField) {
					case 'email':
                        $query->orderBy('email', $orderType);
                        break;
					case 'name':
                        $query->orderBy('name', $orderType);
                        break;
					case 'lastname':
                        $query->orderBy('lastname', $orderType);
                        break;
					case 'birthdate':
                        $query->orderBy('birthdate', $orderType);
                        break;
					case 'phone':
                        $query->orderBy('phone', $orderType);
                        break;
					case 'address':
                        $query->orderBy('address', $orderType);
                        break;
					case 'zipcode':
                        $query->orderBy('zipcode', $orderType);
                        break;
                    case 'created':
                        $query->orderBy('created_at', $orderType);
                        break;
                    case 'updated':
                        $query->orderBy('updated_at', $orderType);
                        break;
					case 'deleted':
						$query->orderBy('deleted_at',$orderType);
						break;
                }
                $cont++;
            }
        }
        return $query;
    }

}

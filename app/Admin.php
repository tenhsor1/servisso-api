<?php

namespace App;

use Illuminate\Auth\Authenticatable;
// use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Foundation\Auth\Access\Authorizable;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;
use App\Extensions\ServissoModel;
class Admin extends ServissoModel implements AuthenticatableContract,
                                    AuthorizableContract,
                                    CanResetPasswordContract
{
    use SoftDeletes;
    use Authenticatable, Authorizable, CanResetPassword;
    protected $table="admins";


    protected $fillable = array('email','name','lastname','addres','phone','zipcode');


    protected $hidden = ['password','state_id','country_id','role_id','update_id','created_at','updated_at'];

    public function news()
    {
        // 1 admin can have multiple news
        return $this->hasMany('App\News');
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
            'mimes' => ':attribute invalid format, allow: jpeg,png,bmp',
            'digits' => ':attribute should be 10 digits',
            'max' => ':attribute length too long',
            'min' => ':attribute length too short',
            'string' => ':attribute should be characters only'
        ];

        return $messages;
    }

    /**
     * Se obtienen las validaciones del modelo Admin
     */
    public static function getValidations(){
        $validation = [
            'email' => 'required|email|max:70|min:11',
            'password' => 'required|max:99|min:7',
            'name' => 'required|max:45|min:4',
            'last_name' => 'max:45|min:4',
            'address' => 'required|max:90|min:10',
            'phone' => 'required|digits:10|max:20|min:10',
            'zipcode' => 'max:10|min:4',
            'state_id' => '',
            'country_id' => '',
            'role_id' => '',
        ];

        return $validation;
    }

	 protected $searchFields = [
        'name',
        'last_name',
		'address',
		'phone',
		'zipcode',
		'state_id',
		'country_id',
		'role_id'
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
		'name',
        'last_name',
		'address',
		'phone',
		'zipcode',
		'state_id',
		'country_id',
		'role_id'
    ];



    public function userable()
    {
      return $this->morphTo();
    }

    /**
     * Used for search using 'LIKE', based on query parameters passed to the
     * request (example: admin?search=test&fields=description,company,address)
     * @param  [QueryBuilder] $query    The consecutive query
     * @param  [Request] $request       The HTTP Request object of the call
     * @param  array  $defaultFields    The default fields if there are no 'searchFields' param passed
     * @return [QueryBuilder]           The new query builder
     */
    public function scopeSearchBy($query, $request, $defaultFields=array('name')){
	   $fields = $this->searchParametersAreValid($request);
        if($fields){
            $search = $request->input('search');
			$where="where";
            $searchFields = is_array($fields) ? $fields : $defaultFields;
            foreach ($searchFields as $searchField) {
                switch ($searchField) {
                    case 'name':
                        //search by the description of the service
                        $query->$where('name', 'LIKE', '%'.$search.'%');
                        break;
					case 'last_name':
                        //search by the description of the service
                        $query->$where('last_name', 'LIKE', '%'.$search.'%');
                        break;
					case 'address':
                        //search by the description of the service
                        $query->$where('address', 'LIKE', '%'.$search.'%');
                        break;
                }
				$where="OrWhere";
            }
        }
        return $query;
    }

    /**
     * Used for search between a end and a start, based on query parameters passed to the
     * request (example: admin?start=2015-11-19&end=2015-12-31&betweenFields=updated,created)
     * @param  [QueryBuilder] $query    The consecutive query
     * @param  [Request] $request       The HTTP Request object of the call
     * @param  array  $defaultFields    The default fields if there are no 'betweenFields' param passed
     * @return [QueryBuilder]           The new query builder
     */
    public function scopeBetweenBy($query, $request, $defaultFields=array('created')){
        $fields = $this->betweenParametersAreValid($request);
        if($fields){
            $start = $request->get('start') . " 00:00:00";
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
     * (example: admin?orderBy=created,updated&orderTypes=ASC,DESC)
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
                    case 'created':
                        $query->orderBy('created_at', $orderType);
                        break;
                    case 'updated':
                        $query->orderBy('updated_at', $orderType);
                        break;
					case 'deleted':
                        $query->orderBy('deleted_at', $orderType);
                        break;
					case 'name':
                        $query->orderBy('name', $orderType);
                        break;
					case 'last_name':
                        $query->orderBy('last_name', $orderType);
                        break;
					case 'address':
                        $query->orderBy('address', $orderType);
                        break;
					case 'phone':
                        $query->orderBy('address', $orderType);
                        break;
					case 'zipcode':
                        $query->orderBy('address', $orderType);
                        break;
					case 'state_id':
                        $query->orderBy('address', $orderType);
                        break;
					case 'country_id':
                        $query->orderBy('address', $orderType);
                        break;
					case 'role_id':
                        $query->orderBy('address', $orderType);
                        break;
                }
                $cont++;
            }
        }
        return $query;
    }
}

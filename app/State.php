<?php

namespace App;
use Illuminate\Database\Eloquent\SoftDeletes;
//use Illuminate\Database\Eloquent\Model;
use App\Extensions\ServissoModel;
class State extends ServissoModel
{
   use SoftDeletes;
	protected $table="states";

	protected $fillable = array('state','abbreviation');


    protected $hidden = ['created_at','role_id','role','updated_at','deleted_at'];

    public function admin()
    {
        // 1 new is related to one admin who created it
        return $this->belongsTo('App\Admin');
    }

	public function partner()
    {
        // 1 new is related to one admin who created it
        return $this->belongsTo('App\Partner');
    }

	public function user()
    {
        // 1 new is related to one admin who created it
        return $this->belongsTo('App\User');
    }

	public function country()
    {
        // 1 new is related to one admin who created it
        return $this->belongsTo('App\Country');
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
     * Se obtienen las validaciones del modelo Partner
     */
    public static function getValidations(){
        $validation = [
            'country_id' => 'required',
            'state' => 'required|max:150|min:3',
            'abbreviation' => ''
        ];

        return $validation;
    }

	 protected $searchFields = [
        'country_id',
	    'state',
        'abbreviation'
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
		'country_id',
        'state',
        'abbreviation'
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
    public function scopeSearchBy($query, $request, $defaultFields=array('state')){
	   $fields = $this->searchParametersAreValid($request);
        if($fields){
            $search = $request->input('search');
			$where="where";
            $searchFields = is_array($fields) ? $fields : $defaultFields;
            foreach ($searchFields as $searchField) {
                switch ($searchField) {
                    case 'state':
                        //search by the description of the country
                        $query->$where('state', 'LIKE', '%'.$search.'%');
                        break;
					case 'abbreviation':
                        //search by the description of the country
                        $query->$where('abbreviation', 'LIKE', '%'.$search.'%');
                        break;
					case 'country_id':
                        //search by the description of the country
                        $query->$where('country_id', 'LIKE', '%'.$search.'%');
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
					case 'state':
                        $query->orderBy('state', $orderType);
                        break;
					case 'abbreviation':
                        $query->orderBy('abbreviation', $orderType);
                        break;
					case 'country_id':
                        $query->orderBy('country_id', $orderType);
                        break;
                }
                $cont++;
            }
        }
        return $query;
    }

}

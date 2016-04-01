<?php

namespace App;

use Illuminate\Database\Eloquent\SoftDeletes;
use App\Extensions\ServissoModel;

class Branch extends ServissoModel
{

	use SoftDeletes;

    protected $table = 'branches';

	protected $fillable = array('address', 'phone', 'latitude','longitude','schedule','company_id','state_id');

	protected $hidden = ['geom', 'deleted_at','created_at','updated_at','role_id','role'];

	protected $searchFields = [
        'address',
        'phone',
        'latitude',
		'longitude',
		'schedule',
        'category',
    ];

    protected $betweenFields = [
        'created',
        'updated',
		'deleted',
    ];

    protected $orderByFields = [
        'created',
        'updated',
		'deleted',
		'address',
		'phone',
		'latitude',
		'longitude',
		'schedule',
    ];


	public function company()
    {
        // 1 branch is related to one company
        return $this->belongsTo('App\Company');
    }

    public function services()
    {
        // 1 branch can have multiple services
        return $this->hasMany('App\Service');
    }

    public function state()
    {
        // 1 branch can have multiple services
        return $this->belongsTo('App\State');
    }

	/**
	* Se obtienen los mensajes de errores
	*/
	public static function getMessages(){
		$messages =
		[
			'required' => ':attribute is required',
			'max' => ':attribute length too long',
			'min' => ':attribute length too short',
			'numeric' => ':attribute should be a number',
            'exists' => ':attribute doesn\t exist',
		];

		return $messages;
	}

	/**
	* Se obtienen las validaciones del modelo Branch
	*/
	public static function getValidations(){
		$validation =
			[
				'address' => 'required|max:59|min:4',
				'phone' => 'required|max:70|min:10',
				'latitude' => 'required|numeric',
				'longitude' => 'required|numeric',
				'email' => 'email|max:70|min:8',
                'schedule' => 'max:99|min:4',
                'state_id' => 'required|integer|exists:states,id',
			];

		return $validation;
	}


    public function setGeomAttribute($value) {
        $this->attributes['geom'] = \DB::raw(sprintf("ST_SetSRID(ST_MakePoint(%s, %s), 4326)", $value[0], $value[1]));
    }

    public function getGeomAttribute(){
        return null;
    }

	/**
     * Used for search using 'LIKE', based on query parameters passed to the
     * request (example: services?search=test&searchFields=description,company,address)
     * @param  [QueryBuilder] $query    The consecutive query
     * @param  [Request] $request       The HTTP Request object of the call
     * @param  array  $defaultFields    The default fields if there are no 'searchFields' param passed
     * @return [QueryBuilder]           The new query builder
     */
    public function scopeSearchBy($query, $request, $defaultFields = array('address')){
        $fields = $this->searchParametersAreValid($request);
        if($fields){
            $search = $request->input('search');
			$where = "where";
            $searchFields = is_array($fields) ? $fields : $defaultFields;
            foreach ($searchFields as $searchField) {
                switch ($searchField) {
                    case 'address':
                        //search by the address of the service
                        $query->$where('address', 'LIKE', '%'.$search.'%');
                        break;
                    case 'phone':
                        //search by the phone of the service
                        $query->$where('phone', 'LIKE', '%'.$search.'%');
                        break;
                    case 'latitude':
                        //search by the latitude of the service
                        $query->$where('latitude', 'LIKE', '%'.$search.'%');
                        break;
					case 'longitude':
						//search by the longitude of the service
                        $query->$where('longitude', 'LIKE', '%'.$search.'%');
						break;
					case 'schedule':
						//search by the schedule of the service
                        $query->$where('schedule', 'LIKE', '%'.$search.'%');
						break;
                    case 'category':
                        //search by the schedule of the service
                        $query->$where('categories.id', '=', $search);
                        break;
                }
				$where = "orWhere";
            }
        }
        return $query;
    }

    public function scopeCategory($query, $request, $defaultFields = array('address')){
        $category = $request->input('category-id');
        if(isset($category)){
            $categoryId = (int) $request->input('category-id');
            $search = $request->input('search');
            $query->where('categories.id', '=', $categoryId);
        }
        return $query;
    }

    public function scopeWithin($query, $request){
        $within = $this->withinParametersAreValid($request);
        if($within){
            $bottomLimit = $within[0];
            $topLimit = $within[1];

            $bottomLatitude = $bottomLimit[0]; //a
            $bottomLongitude = $bottomLimit[1]; //b
            $topLatitude = $topLimit[0]; //c
            $topLongitude = $topLimit[1]; //d
            $query->whereRaw("ST_Intersects(geom,
                ST_SETSRID(ST_MakeBox2D(
                    ST_SetSRID(ST_MakePoint(?, ?),4326), ST_SetSRID(ST_MakePoint(?, ?), 4326)
                ), 4326)
                )", [$bottomLongitude, $bottomLatitude, $topLongitude, $topLatitude]);
            /*
            $query->whereRaw("
                (? < ? AND latitude BETWEEN ? AND ?)
                    OR (? < ? AND latitude BETWEEN ? AND ?)
                AND
                (? < ? AND longitude BETWEEN ? AND ?)
                    OR (? < ? AND longitude BETWEEN ? AND ?)",
                [$bottomLatitude, $topLatitude, $bottomLatitude, $topLatitude,
                $topLatitude, $bottomLatitude, $topLatitude, $bottomLatitude,
                $bottomLongitude, $topLongitude, $bottomLongitude, $topLongitude,
                $topLongitude, $bottomLongitude, $topLongitude, $bottomLongitude]);*/
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
    public function scopeBetweenBy($query, $request, $defaultFields = array('created')){
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
					case 'address':
                        $query->orderBy('branches.address', $orderType);
                        break;
					case 'phone':
                        $query->orderBy('branches.phone', $orderType);
                        break;
					case 'latitude':
                        $query->orderBy('branches.latitude', $orderType);
                        break;
					case 'longitude':
                        $query->orderBy('branches.longitude', $orderType);
                        break;
					case 'schedule':
                        $query->orderBy('branches.schedule', $orderType);
                        break;
                    case 'created':
                        $query->orderBy('branches.created_at', $orderType);
                        break;
                    case 'updated':
                        $query->orderBy('branches.updated_at', $orderType);
                        break;
					case 'deleted':
						$query->orderBy('branches.deleted_at', $orderType);
						break;
                }
                $cont++;
            }
        }
        return $query;
    }
}

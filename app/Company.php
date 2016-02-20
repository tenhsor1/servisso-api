<?php

namespace App;

//use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Extensions\ServissoModel;

class Company extends ServissoModel
{
	use SoftDeletes;
    protected $table = 'companies';

	protected $fillable = ['name','description','companiescol','partner_id','category_id','image','thumbnail'];

	protected $hidden = ['partner_id','deleted_at','created_at','updated_at','role_id','role'];

	protected $searchFields = [
        'name',
        'description'
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
		'description'
    ];

	public function partner()
    {
        // 1 company is related to one partner
        return $this->belongsTo('App\Partner');
    }

    public function category()
    {
        // 1 company is related to one category
        return $this->belongsTo('App\Category');
    }

    public function branches()
    {
        // 1 company can have multiple branches
        return $this->hasMany('App\Branch');
    }

	/**
	* Se obtienen los mensajes de errores
	*/
	public static function getMessages(){
		$messages =
		[
			'required' => ':attribute is required',
			'mimes' => ':attribute invalid format, allow: jpeg,png,bmp',
			'max' => ':attribute length too long',
			'min' => ':attribute length too short',
		];

		return $messages;
	}

	/**
	* Se obtienen las validaciones del modelo Partner
	*/
	public static function getValidations(){
		$validation =
			[
				'name' => 'required|max:59|min:4',
				'description' => 'required|max:499|min:4',
				'category_id' => 'required'
			];

		return $validation;
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
                    case 'name':
                        //search by the name of the service
                        $query->$where('email', 'LIKE', '%'.$search.'%');
                        break;
                    case 'description':
                        //search by the description of the service
                        $query->$where('description', 'LIKE', '%'.$search.'%');
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
					case 'name':
                        $query->orderBy('name', $orderType);
                        break;
					case 'description':
                        $query->orderBy('description', $orderType);
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

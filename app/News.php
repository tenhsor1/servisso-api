<?php

namespace App;

// use Illuminate\Database\Eloquent\Model;
use App\Extensions\ServissoModel;
use Illuminate\Database\Eloquent\SoftDeletes;
class News extends ServissoModel 
{
    use SoftDeletes;
	protected $table="news";
	

    protected $fillable = array('admin_id','title','content','image');


    protected $hidden = ['status','created_at','role_id','role','updated_at','deleted_at'];

    public function admin()
    {
        // 1 new is related to one admin who created it
        return $this->belongsTo('App\Admin');
    }

    public function comments()
    {
        // 1 new can have multiple comments
        return $this->hasMany('App\NewComment');
    }

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
            'admin_id' => '',
            'title' => 'required|max:45|min:5',
            'content' => 'required|max:500|min:7',
            'image' => 'max:45|min:4',
            'status' => '',
            'role_id' => '',
            'role' => ''
			];


        return $validation;
    }
	
	 protected $searchFields = [
        'admin_id',
		'title',
		'content'
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
		'admin_id',
		'title'
    ];



    public function userable()
    {
      return $this->morphTo();
    }


    /**
     * Used for search using 'LIKE', based on query parameters passed to the
     * request (example: news?search=test&fields=title,content) 
     * @param  [QueryBuilder] $query    The consecutive query
     * @param  [Request] $request       The HTTP Request object of the call
     * @param  array  $defaultFields    The default fields if there are no 'searchFields' param passed
     * @return [QueryBuilder]           The new query builder
     */
    public function scopeSearchBy($query, $request, $defaultFields=array('title')){      
	   $fields = $this->searchParametersAreValid($request);
        if($fields){   
            $search = $request->input('search');
			$where="where"; 
            $searchFields = is_array($fields) ? $fields : $defaultFields;
            foreach ($searchFields as $searchField) {
                switch ($searchField) {
                    case 'title':
                        //search by the description of the title
                        $query->$where('title', 'LIKE', '%'.$search.'%');
                        break;
					case 'content':
                        //search by the description of the content
                        $query->$where('content', 'LIKE', '%'.$search.'%');
                        break;
					case 'admin_id':
                        //search by the description of the admin_id
                        $query->$where('admin_id', 'LIKE', '%'.$search.'%');
                        break;
                } 
				$where="OrWhere";
            }
        }
        return $query;
    }

    /**
     * Used for search between a end and a start, based on query parameters passed to the
     * request (example: news?start=2015-11-19&end=2015-12-31&betweenFields=updated,created)
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
     * (example: news?orderBy=created,updated&orderTypes=ASC,DESC) 
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
					case 'admin_id':
                        $query->orderBy('admin_id', $orderType);  
                        break;
					case 'title':
                        $query->orderBy('title', $orderType);
                        break;	
                }
                $cont++;
            }
        }
        return $query;
    }
}

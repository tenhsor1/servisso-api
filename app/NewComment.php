<?php

namespace App;

// use Illuminate\Database\Eloquent\Model;
use App\Extensions\ServissoModel;
use Illuminate\Database\Eloquent\SoftDeletes;
class NewComment extends ServissoModel
{
    use SoftDeletes;
	protected $table="news_comments";
	

    protected $fillable = array('news_id','user_type','user_id','comment');


    protected $hidden = ['created_at','role_id','role','updated_at','deleted_at'];

    public function news()
    {
        // 1 comment is related to one new
        return $this->belongsTo('App\News');
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
			'news_id' => 'required',
            'user_id' => '',
            'comment' => 'required|max:500|min:7',
            'user_type' => '',
            'role_id' => '',
            'role' => ''
			];


        return $validation;
    }
	
	 protected $searchFields = [
        'news_id',
        'comment'
    ];

    protected $betweenFields = [
        'created',
        'updated'
    ];

    protected $orderByFields = [
        'created',
        'updated',
		'news_id'
    ];



    public function userable()
    {
      return $this->morphTo();
    }

 
    /**
     * Used for search using 'LIKE', based on query parameters passed to the
     * request (example: newcomment?search=test&fields=id_new,comment)
     * @param  [QueryBuilder] $query    The consecutive query
     * @param  [Request] $request       The HTTP Request object of the call
     * @param  array  $defaultFields    The default fields if there are no 'searchFields' param passed
     * @return [QueryBuilder]           The new query builder
     */
    public function scopeSearchBy($query, $request, $defaultFields=array('comment')){
	$where="where";       
	   $fields = $this->searchParametersAreValid($request);
        if($fields){   
            $search = $request->input('search');
            $searchFields = is_array($fields) ? $fields : $defaultFields;
            foreach ($searchFields as $searchField) {
                switch ($searchField) {
                    case 'comment':
                        //search by the description of the service
                        $query->$where('comment', 'LIKE', '%'.$search.'%');
						$where="OrWhere";
                        break;
					case 'news_id':
                        //search by the description of the service
                        $query->$where('news_id', 'LIKE', '%'.$search.'%');
						$where="OrWhere";
                        break;
                    
                }  
            }
        }
        return $query;
    }

    /**
     * Used for search between a end and a start, based on query parameters passed to the
     * request (example: newcomment?start=2015-11-19&end=2015-12-31&betweenFields=updated,created)
     * @param  [QueryBuilder] $query    The consecutive query
     * @param  [Request] $request       The HTTP Request object of the call
     * @param  array  $defaultFields    The default fields if there are no 'betweenFields' param passed
     * @return [QueryBuilder]           The new query builder
     */
    public function scopeBetweenBy($query, $request, $defaultFields=array('created')){
        $fields = $this->betweenParametersAreValid($request);
        if($fields){
            $start = $request->get('start');
            $end = $request->get('end');
            $searchFields = is_array($fields) ? $fields : $defaultFields;
            foreach ($searchFields as $searchField) {
                switch ($searchField) {
                    case 'created':
                        //search depending on the creation time
                        if($start)
                            $query->where('created_at', '>=', $start);
                        if($end)
                            $query->where('created_at', '<=', $end);
                        break;
                    case 'updated':
                        //search depending on the updated time
                        if($start)
                            $query->where('updated_at', '>=', $start);
                        if($end)
                            $query->where('updated_at', '<=', $end);
                        break;
                }
            }
        } 
        return $query;
    }

    /**
     * Used for ordering the result of a get request
     * (example: newcomment?orderBy=created,updated&orderTypes=ASC,DESC) 
     * @param  [QueryBuilder] $query    The consecutive query
     * @param  [Request] $request       The HTTP Request object of the call
     * @return [QueryBuilder]           The new query builder
     */
    public function scopeOrderByCustom($query, $request){
        $orderFields = $this->orderByParametersAreValid($request);
        if($orderFields){
            $orderTypes = explode(',', $request->input('orderTypes'));
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
					
					case 'news_id':
                        $query->orderBy('news_id', $orderType);  
                        break;
                }
                $cont++;
            }
        }
        return $query;
    }
}

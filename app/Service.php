<?php

namespace App;

//use Illuminate\Database\Eloquent\Model;
use App\Extensions\ServissoModel;
use Illuminate\Database\Eloquent\SoftDeletes;
use Log;

class Service extends ServissoModel
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'services';
    use SoftDeletes;

    /**
     * The attributes that are not mass assignable.
     *
     * @var array
     */
    protected $guarded = ['service_id'
                            , 'branch_id'
                            , 'userable_id'
                            , 'userable_type'
                        ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [   'branch_id'
                            , 'userable_type'
                            , 'deleted_at'
                            , 'created_at'
                            , 'updated_at'
                        ];

    protected $searchFields = [
        'address',
        'company',
        'description'
    ];

    protected $betweenFields = [
        'created',
        'updated'
    ];

    protected $orderByFields = [
        'created',
        'updated'
    ];

    public function branch(){
        //1 service is related to one branch
        return $this->belongsTo('App\Branch');
    }

	public function userRate(){
		//1 sevice is related to one user rate
		return $this->hasOne('App\UserRate');
	}

	public function partnerRate(){
		//1 service rate is related to one partner rate
		return $this->hasOne('App\PartnerRate');
	}

    public function userable()
    {
      return $this->morphTo();
    }

    public function scopeWherePartner($query, $partnerId)
    {
        return $query->leftJoin('branches','branches.id','=','services.branch_id')
              ->leftJoin('companies','companies.id','=','branches.company_id')
              ->leftJoin('partners','partners.id','=','companies.partner_id')
              ->where('partners.id', $partnerId)
              ->select('services.*');
    }

    public function scopeWhereBranch($query, $branchId)
    {
        return $query->leftJoin('branches','branches.id','=','services.branch_id')
              ->where('branches.id', $branchId)
              ->select('services.*');
    }

    /**
     * Used for search using 'LIKE', based on query parameters passed to the
     * request (example: services?search=test&searchFields=description,company,address)
     * @param  [QueryBuilder] $query    The consecutive query
     * @param  [Request] $request       The HTTP Request object of the call
     * @param  array  $defaultFields    The default fields if there are no 'searchFields' param passed
     * @return [QueryBuilder]           The new query builder
     */
    public function scopeSearchBy($query, $request, $defaultFields=array('description')){
        $fields = $this->searchParametersAreValid($request);
        if($fields){
            $search = $request->input('search');
            $searchFields = is_array($fields) ? $fields : $defaultFields;
            foreach ($searchFields as $searchField) {
                switch ($searchField) {
                    case 'description':
                        //search by the description of the service
                        $query->orWhere('description', 'LIKE', '%'.$search.'%');
                        break;
                    case 'address':
                        //search for the address of the branch related to the service
                        $query->orWhereHas('branch', function($query) use ($search){
                            $query->where('address', 'LIKE', '%'.$search.'%');
                        });
                        break;
                    case 'company':
                        //search for the company name related to the service
                        $query->orWhereHas('branch', function($query) use ($search){
                            $query->whereHas('company', function($query) use ($search){
                                $query->where('name', 'LIKE', '%'.$search.'%');
                            });
                        });
                        break;
                }
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
     * (example: services?orderBy=created,updated&orderTypes=ASC,DESC)
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
                }
                $cont++;
            }
        }
        return $query;
    }
}

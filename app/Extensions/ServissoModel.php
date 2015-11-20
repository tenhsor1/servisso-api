<?php
namespace App\Extensions;

use Illuminate\Database\Eloquent\Model;

/**
* Extension of Eloquent model for implement generic methods used for servisso
*/
class ServissoModel extends Model
{

    protected $orderTypes = [
        'ASC',
        'DESC'
    ];

    /**
     * Check that the query parameters for search are valid if there is any
     * @param  [Request] $request HTTP Request with the query parameters in it
     * @return [mixed] False if no 'search' param, null if 'searchFields' are wrong,
     *                 True if no 'searchFields' param, array if 'searchFields' and they are correct
     */
    protected function searchParametersAreValid($request){
        /**
         * Check if search parameters are valid, if fields were passed on the request
         * then return them as an array
         */
        if($request->input('search')){
            $fields = array();
            if($request->input('fields')){
                $fieldsString = $request->input('fields');
                $fields = explode(',', $fieldsString);
                if(count(array_intersect($this->searchFields, $fields)) != count($fields)){
                    abort(422, "The fields trying to be searched are not correct");
                    return null;
                }
            }
            if($fields) return $fields;
            return True;
        }
        return False;
    }

    /**
     * Check if between parameters are valid, if fields were passed on the request
     * @param  [Request] $request HTTP Request with the query parameters in it
     * @return [mixed] False if no 'start' or 'end' params, null if 'betweenFields' are wrong,
     *                 True if no 'betweenFields' param, array if 'betweenFields' and they are correct
     */
    protected function betweenParametersAreValid($request){
        if($request->input('start') || $request->input('end')){
            $fields = array();
            if($request->input('fieldsBetween')){
                $fieldsString = $request->input('fieldsBetween');
                $fields = explode(',', $fieldsString);
                if(count(array_intersect($this->betweenFields, $fields)) != count($fields)){
                    abort(422, "The fields trying to be searched are not correct");
                    return null;
                }
            }
            if($fields) return $fields;
            return True;
        }
        return False;
    }

    /**
     * Check if orderBy is passed as an query param, and they are valid ones
     * @param  [Request] $request HTTP Request with the query parameters in it
     * @return [mixed] False if no 'orderBy' param, null if 'orderBy' or 'orderTypes' are wrong,
     *                 array if 'orderBy' is passed and they are correct
     */
    protected function orderByParametersAreValid($request){
        if($request->input('orderBy')){
            $fields = array();
            $fieldsString = $request->input('orderBy');
            $fields = explode(',', $fieldsString);
            if(count(array_intersect($this->orderByFields, $fields)) != count($fields)){
                abort(422, "The fields trying to be ordered are not correct");
                return null;
            }
            if($request->input('orderType')){
                $orderTypesString = $request->input('orderType');
                $orderTypes = explode(',', $orderTypesString);
                if(count(array_intersect($this->orderTypes, $orderTypes)) != count($orderTypes)){
                    abort(422, "The order types are not correct");
                    return null;
                }
            }
            return $fields;
        }
        return False;
    }
}
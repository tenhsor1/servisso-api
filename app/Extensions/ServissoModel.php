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
            if(!$this->isValidSearch($request->input('search'))){
                abort(400, "The value for search can only have alphanumeric values, and spaces");
                return null;
            }
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
     * Check that the query parameters for search based on latitude and logitude within is correct
     * @param  [Request] $request HTTP Request with the query parameters in it
     * @return [mixed] False if no 'within' param, null if 'searchFields' are wrong,
     *                 True if no 'searchFields' param, array if 'searchFields' and they are correct
     */
    protected function withinParametersAreValid($request){
        /**
         * Check if search parameters are valid, if fields were passed on the request
         * then return them as an array
         */
        if($request->input('withinTop') && $request->input('withinBottom')){
            $withinTopArray = explode(',', $request->input('withinTop'));
            $withinBottomArray = explode(',', $request->input('withinBottom'));

            if(count($withinTopArray) != 2 || count($withinBottomArray) != 2){
                abort(400, "The within limits doesn't have the correct number of parameters");
                return null;
            }
            $topLatitude = $withinTopArray[0];
            $topLongitude = $withinTopArray[1];
            $bottomLatitude = $withinBottomArray[0];
            $bottomLongitude = $withinBottomArray[1];

            if(!is_numeric($topLatitude) || !is_numeric($topLongitude) || !is_numeric($bottomLatitude) || !is_numeric($bottomLongitude)){
                abort(400, "The within limits are not correct numbers");
                return null;
            }

            return [[$bottomLatitude, $bottomLongitude], [$topLatitude, $topLongitude]];
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
			$orderByFields = ($request->input('orderBy')) ? $request->input('orderBy') : 'created';
            $fields = array();
            $fields = explode(',', $orderByFields );
            if(count(array_intersect($this->orderByFields, $fields)) != count($fields)){
                abort(422, "The fields trying to be ordered are not correct");
                return null;
            }

			$orderFields = ($request->input('orderType')) ? $request->input('orderType') : 'desc';
            $orderTypesString = strtoupper($orderFields);
            $orderTypes = explode(',', $orderTypesString);
            if(count(array_intersect($this->orderTypes, $orderTypes)) != count($orderTypes)){
				abort(422, "The order types are not correct");
                return null;
            }

         return $fields;
    }

	/**
	* $value = field value
	* return true: if value has a correct format
			 false: if value has an incorrect format
	*/
	protected function isValidSearch($value){
		//permite numeros '0' hasta '9', minusculas de 'a' hasta 'z' y signo '+' entre palabras. 'mecanico' o 'mecanico+elec+etc...'
		$pattern = "/^([a-z0-9](\+[a-z0-9])?)+$/";

		if(preg_match($pattern,$value))
			return true;

		return false;
	}

	protected function isValidDate($value){
		//permite solo numeros y guiones, el rango es del 2015 hasta 2039 con el formato: 2015-21-11(yyyy-mm-dd)
		$pattern = "/^2015-(0[1-9]|1[0-2])-(0[1-9]|1[0-9]|2[0-9]|3[0-1])$/";

		if(preg_match($pattern,$value))
			return true;

		return false;
	}

	protected function isValidField($value, $fields = array()){
		$pattern = "";

		if($fields){
			//valida fields,dateFields,orderBy: valida que solo esten los campos especificados en el array
			$fields = implode('|',$fields);
			$fields2 = ','.implode('|,',$fields);
			$pattern_fields_parameters = "/^\(($fields)(($fields2)?)+\)$/";
		}else{
			//valida fields,dateFields,orderBy: permite numeros '0' hasta '9', minusculas de 'a' hasta 'z' y 'coma' entre palabras.
			//formato: (name) o (email,name,etc...) o (created,updated)
			$pattern = "/^\(([a-z0-9](,[a-z0-9])?)+\)$/";
		}

		if(preg_match($pattern,$value))
			return true;

		return false;
	}

	protected function isValidOrder($value){
		//valida orderType: permite solo asc y desc en minuscula.
		//formato: (asc) o (desc) o (asc,desc,etc...)
		$pattern = "/^\((asc|desc)((,asc|,desc)?)+\)$/";

		if(preg_match($pattern,$value))
			return true;

		return false;
	}

	protected function isValidLimit($value){
		//valida limit o page: permite numeros '1' hasta '100'
		$pattern = "/^([1-9][0-9]?|100)$/";

		if(preg_match($pattern,$value))
			return true;

		return false;
	}

    /**
     * Check if limit and pages is passed as query params, and they are valid ones
     * @param  [Request] $request HTTP Request with the query parameters in it
     * @return [mixed] False if no 'limit' param, null if 'limit' or 'page' are wrong,
     *                 True if 'limit' or 'page' are correct
     */
    protected function limitParametersAreValid($request){
        if($request->input('limit')){
            $limit = (int)$request->input('limit');
            if($limit){
                if($limit > 2000){
                    abort(422, "The limit can't be bigger than 2000");
                        return null;
                }
                if($request->input('page')){
                    $page = (int)$request->input('page');
                    if(!$page or $page == 0){
                        abort(422, "The page must be an integer and bigger than zero");
                        return null;
                    }
                }
                return True;
            }else{
                abort(422, "The limit must be an integer and greater than 0");
                return null;
            }
        }
        return False;
    }

    public function scopeLimit($query, $request){
        if($this->limitParametersAreValid($request)){
            $limit = $request->input('limit');
			if($page = $request->input('page')){
                $page = $page - 1;
				$page = $page * $limit;
				$query->skip($page)->take($limit);
			}else{
				$query->take($limit);
			}
        }else{
            //if not limit passed, then just show 2000 results as max
            $query->take(2000);
        }
        return $query;
    }
}
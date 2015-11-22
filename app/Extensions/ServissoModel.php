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
}
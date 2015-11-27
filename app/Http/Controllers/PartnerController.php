<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use Validator;
use App\Partner;
use JWTAuth;
	
class PartnerController extends Controller
{
	
	public function __construct(){
        $this->middleware('jwt.auth:partner|admin', ['only' => ['show','destroy','update']]);
        $this->middleware('default.headers');
		$this->user_roles = \Config::get('app.user_roles');
    }	
	
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
		$url = $_SERVER['REQUEST_URI'];
		$pattern_number = Partner::getUrlPattern($url);
		
		//LA URL NO TIENE UN FORMATO CORRECTO
		if($pattern_number == 0){
			$response = ['error' => 'Invalid url', 'code' => 404];
			return response()->json($response,200);
		}
		
		$base_url = explode('?',$url);
		
		if(count($base_url) <= 1){
			//NORMAL URL WITHOUT PARAMETERS			
			$partners = Partner::with('companies')->get();
			$count = $partners->count();
			$response = ['code' => 200,'count' => $count,'data' => $partners];
		
			return response()->json($response,200);
		}
		
		//IF THE URL HAS ONE OR MORE PARAMETERS CONTINUES WITH THE FLOW
		$parameters = explode('&',$base_url[1]);
		
		$search = $fields = $start = $end = $dateFields = $orderBy = $orderType = $limit = $page = '';
		$errors = array();
		
		for($i = 0;$i < count($parameters);$i++){
			
			if(strpos($parameters[$i],'search') !== false){
				//SEARCH
				$search = str_replace('search=','',$parameters[$i]);
				$search = str_replace('+',' ',$search);
				
			}else if(strpos($parameters[$i],'fields') !== false){
				//FIELDS
				$fields = str_replace(array('fields=','(',')'),'',$parameters[$i]);
					$fields = explode(',',strtolower($fields));
					$validFields = Partner::getValidFields();
					
					//SE VALIDA QUE LOS FIELDS SOLICITADOS SEAN FIELDS PERIMITIDOS
					foreach($fields as $field){
						if(!in_array($field,$validFields)){
							$errors[] = "Invalid field: ".$field;
							break;
						}
					}
					
					$result = '(';												
						
					for($a = 0;$a < count($fields);$a++){
						$result .= $fields[$a]." LIKE '%".$search."%' ";
							
						if(($a + 1) < count($fields))
							$result .= "OR ";
					}

					$result .= ")";
						
				$fields = $result;
			}else if(strpos($parameters[$i],'start') !== false){
				//START
				$start = str_replace('start=','',$parameters[$i]);
				$start = strtotime($start);
				
			}else if(strpos($parameters[$i],'end') !== false){
				//END
				$end = str_replace('end=','',$parameters[$i]);			
				$end = strtotime($end);
				
				if($start > $end){
					$errors[] = 'start date too big';
					break;
				}
				
				$end = "AND ".$end;
				
			}else if(strpos($parameters[$i],'dateFields') !== false){
				//DATE FIELDS
				$dateFields = str_replace(array('dateFields=','(',')'),'',$parameters[$i]);
				$dateFields = explode(',',$dateFields);
				$result = '(';				

				$query_part = ">=";
				if($end != ''){
					$query_part = "BETWEEN";
				}
						
				for($a = 0;$a < count($dateFields);$a++){
					$result .= $dateFields[$a]." $query_part $start $end";
							
					if(($a + 1) < count($dateFields))
						$result .= " AND ";
				}

				$result .= ")";
						
				$dateFields = $result;
			}else if(strpos($parameters[$i],'orderBy') !== false){
				//ORDER BY
				$orderBy = "ORDER BY ".str_replace(array('orderBy=','(',')'),'',$parameters[$i]);
				
			}else if(strpos($parameters[$i],'orderType') !== false){
				//ORDER TYPE
				$orderType = str_replace(array('orderType=','(',')'),'',$parameters[$i]);
				
			}else if(strpos($parameters[$i],'limit') !== false){
				//LIMIT
				$limit = "LIMIT ".str_replace('limit=','',$parameters[$i]);
				
			}else if(strpos($parameters[$i],'page') !== false){
				//PAGE
				$page = str_replace('page=','',$parameters[$i]);				
			}else{
				
			}
		}
		
		if(!$orderBy)
			$orderBy = "ORDER BY id";//default, just in case
		
		if(!$orderType)
			$orderType = "DESC";//default, just in case
		
		if(count($errors) > 0){
			$response = ['error' => $errors,'patron' => $pattern_number,'code' => 422];	
			return response()->json($response,422);
		}
		
		$query = "";
		switch($pattern_number){
			case 1: $query = "SELECT * FROM partner"; break;
			case 2: $query = "SELECT * FROM partner $orderBy $orderType $limit"; break;			
			case 3: 
				$fields = ($fields) ? "WHERE ".$fields : "";
				$query = "SELECT * FROM partner $fields $orderBy $orderType $limit"; break;
			case 4: 
				$start = ($end) ? "BETWEEN ".$start : ">= ".$start;
				$query = "SELECT * FROM partner WHERE date $start $end $orderBy $orderType $limit"; break;
			case 5: 
				$fields .= ($fields) ? " AND" : ""; 
				$query = "SELECT * FROM partners WHERE $fields $dateFields $orderBy $orderType $limit";
		}
		
		$response = ['data' => $query,'patron' => $pattern_number,'code' => 200];	
		return response()->json($response,200);

    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
       // 
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {			

		$messages = Partner::getMessages();
		$validation = Partner::getValidations();
		
        $v = Validator::make($request->all(),$validation,$messages);		
		
		$response = ['error' => $v->messages(), 'code' =>  422];
		
		//SE VERIFICA SI ALGUN CAMPO NO ESTA CORRECTO
		if($v->fails()){
			//return response()->json($v->messages(),200);			
			return response()->json($response,422);
		}
		
		$partner = new Partner;
		$partner->email = $request->email;
		$partner->password = $request->password;
		$partner->name = $request->name;
		$partner->lastname = $request->lastname;
		$partner->birthdate = $request->birthdate;
		$partner->phone = $request->phone;
		$partner->address = $request->address;
		$partner->zipcode = $request->zipcode;
		$partner->state_id = $request->state_id;
		$partner->country_id = $request->country_id;
		$partner->status = $request->status;
		$partner->plan_id = $request->plan_id;
		
		//SE CREA PARTNER
		$save = $partner->save();
		
		//TOKEN ES CREADO
		$extraClaims = ['role'=>'PARTNER'];
        $token = JWTAuth::fromUser($partner,$extraClaims);
        $reflector = new \ReflectionClass('JWTAuth');
        $partner->token = $token;
		
		if($save != false){
			$response = ['data' => $partner,'code' => 200,'message' => 'Partner was created succefully'];
			return response()->json($response,200);
		}else{
			$response = ['error' => 'It has occurred an error trying to save the partner','code' => 404];
			return response()->json($response,404);
		}
		
		
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
		$userRequested = \Auth::User();
		
		//SE VERIFICA QUE EL PARTNER QUE HIZO LA PETICION SOLO PUEDA OBTENER INFO DE EL MISMO
		if($userRequested->id == $id || $userRequested->roleAuth  == "ADMIN"){
			
			$partner = Partner::find($id);
			
			//SE VERIFICA QUE EL PARTNER EXISTA
			if(!is_null($partner)){
				$response = ['code' => 200,'data' => $partner];
				return response()->json($response,200);
			}else{
				$response = ['error' => 'Partner does no exist','code' => 422];
				return response()->json($response,422);
			}
			
		}else{
            $response = ['error'   => 'Unauthorized','code' => 403];
            return response()->json($response, 403);
        }		
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
		//     
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
		$userRequested = \Auth::User();		
		
		//SE VERIFICA QUE EL PARTNER QUE HIZO LA PETICION SOLO PUEDA ACTUALIZARSE EL MISMO
		if($userRequested->id == $id || $userRequested->roleAuth  == "ADMIN"){						
			
			$partner = Partner::find($id);
			
			//SE VERIFICA QUE EL PARTNER EXISTA
			if(!is_null($partner)){
				
				$messages = Partner::getMessages();
				$validation = Partner::getValidations();
				
				$v = Validator::make($request->all(),$validation,$messages);	
				
				//SE VERIFICA SI ALGUN CAMPO NO ESTA CORRECTO
				if($v->fails()){
					$response = ['error' => $v->messages(),'code' => 404];
					return response()->json($response,404);
				}

				$partner->email = $request->email;
				$partner->password = $request->password;
				$partner->name = $request->name;
				$partner->lastname = $request->lastname;
				$partner->birthdate = $request->birthdate;
				$partner->phone = $request->phone;
				$partner->address = $request->address;
				$partner->zipcode = $request->zipcode;
				$partner->state_id = $request->state_id;
				$partner->country_id = $request->country_id;
				$partner->status = $request->status;
				$partner->plan_id = $request->plan_id;
				$partner->role_id = $userRequested->id;
				$partner->role = $this->user_roles[$userRequested->roleAuth];
				$partner->save();
				
				if($partner != false){
					$response = ['data' => $partner,'code' => 200,'message' => "Partner was updated succefully"];
					return response()->json($response,200);
				}else{
					$response = ['error' => 'It has occurred an error trying to update the partner','code' => 404];
					return response()->json($response,404);
				}
				
				
			}else{
				//EN DADO CASO QUE EL ID DEL PARTNER NO SE HALLA ENCONTRADO
				$response = ['error' => 'Partner does not exist','code' => 422];
				return response()->json($response,422);
			}
			
		}else{
            $response = ['error'   => 'Unauthorized','code' => 403];
            return response()->json($response, 403);
        }			       
		
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
		
		$userRequested = \Auth::User();	
		
		if($userRequested->roleAuth  == "ADMIN"){
			
			$partner = Partner::find($id);
			if(!is_null($partner)){
				
				$partner->role_id = $userRequested->id;
				$partner->role = $this->user_roles[$userRequested->roleAuth];
				$partner->save();
				
				//SE BORRAR EL PARTNER
				$row = $partner->delete();
				
				if($row != false){
					$response = ['code' => 200,'message' => "Partner was deleted succefully"];
					return response()->json($response,200);
				}else{
					$response = ['error' => 'It has occurred an error trying to delete the partner','code' => 404];
					return response()->json($response,404);
				}
			
			}else{
				//EN DADO CASO QUE EL ID DEL PARTNER NO SE HALLA ENCONTRADO
				$response = ['error' => 'Partner does not exist','code' => '404'];
				return response()->json($response,404);
			}
		}else{
			$response = ['error'   => 'Unauthorized','code' => 403];
            return response()->json($response, 403);
		}
	}
	
}

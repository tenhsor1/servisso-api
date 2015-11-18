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
        $this->middleware('jwt.auth:partner', ['only' => ['show','destroy','update']]);
        $this->middleware('default.headers');
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
		
		//PATRON 1
		if($pattern_number == 1){
			
		$partners = Partner::with('companies')->get();
		$response = ['data' => $partners,'code' => 200];
		
		return response()->json($response,200);
		
		//PATRON 2
		}else if($pattern_number == 2){
			
			$pattern2 = Partner::getPattern(2);
			preg_match($pattern2,$url,$matches,PREG_OFFSET_CAPTURE);
			
			$limit = $page = $orderBy = $orderType = '';

			for($i = 0; $i < count($matches);$i++){
				
				if(strpos($matches[$i][0],'limit')){
					$limit = "LIMIT ".str_replace('?limit=','',$matches[$i][0]);
				}else if(strpos($matches[$i][0],'page')){
					$page = str_replace('&page=','',$matches[$i][0]);
				}else if(strpos($matches[$i][0],'orderBy')){
					$orderBy = "ORDER BY ".str_replace(array('&orderBy=','(',')'),'',$matches[$i][0]);
				}else if(strpos($matches[$i][0],'orderType')){
					$orderType = str_replace(array('&orderType=','(',')'),'',$matches[$i][0]);
				}
			}
			
			$query = "SELECT * FROM user $orderBy $orderType $limit";
			
			$response = ['data' => $query, 'code' => 200];
			return response()->json($response,200);
			
		//PATRON 3
		}else if($pattern_number == 3){
				
			$pattern3 = Partner::getPattern(3);
			preg_match($pattern3,$url,$matches,PREG_OFFSET_CAPTURE);
			
			$search = $fields = $orderBy = $orderType = $limit = $page  = '';		

			for($i = 0; $i < count($matches);$i++){
				
				if($i > 0){				
				
					if(strpos($matches[$i][0],'search')){
						$search = str_replace('?search=','',$matches[$i][0]);
						$search = str_replace('+',' ',$search);												
				
					}else if(strpos($matches[$i][0],'fields')){
						
						$fields = str_replace(array('&fields=','(',')'),'',$matches[$i][0]);
						$fields = explode(',',$fields);
						$result = '(';												
						
						for($a = 0;$a < count($fields);$a++){
							$result .= $fields[$a]." LIKE '%".$search."%' ";
							
							if(($a + 1) < count($fields))
								$result .= "OR ";
						}

						$result .= ")";
						
						$fields = $result;
						
					}else if(strpos($matches[$i][0],'orderBy')){
						$orderBy = "ORDER BY ".str_replace(array('&orderBy=','(',')'),'',$matches[$i][0]);						
					}else if(strpos($matches[$i][0],'orderType')){
						$orderType = str_replace(array('&orderType=','(',')'),'',$matches[$i][0]);					
					}else if(strpos($matches[$i][0],'limit')){
						$limit = "LIMIT ".str_replace('?limit=','',$matches[$i][0]);
					}else if(strpos($matches[$i][0],'page')){
						$page = str_replace('&page=','',$matches[$i][0]);
					}
				}							
			}			
				
			$query = "SELECT * FROM user WHERE $fields $orderBy $orderType $limit";
				
			$response = ['data' => $query, 'code' => 200];
			return response()->json($response,200);
		
		}else if($pattern_number == 4){
			//AUN FALTA CODEAR ESTE PATRON, EN TODOS SE APLICA LA MISMA LOGICA
		}else if($pattern_number == 5){
			//AUN FALTA CODEAR ESTE ULTIMO PATRON
		}else{
			$response = ['error' => 'Invalid url', 'code' => 404];
			return response()->json($response,200);
		}

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
		if($userRequested->id == $id){
			
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
		$partnerRequested = \Auth::User();
		
		//SE VERIFICA QUE EL PARTNER QUE HIZO LA PETICION SOLO PUEDA ACTUALIZARSE EL MISMO
		if($partnerRequested->id == $id){
			
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
				
				//SE ACTUALIZA PARTNER
				$row = $partner->update($request->all());
				
				if($row != false){
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
        $partner = Partner::find($id);
		if(!is_null($partner)){
			
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
    }
	
}

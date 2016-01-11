<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\UserRate;
use JWTAuth;
use Validator;
use App\Service;
use App\Branch;
use App\Partner;
use App\User;
use App\Company;

class UserRateController extends Controller
{
	
	public function __construct(){
        $this->middleware('jwt.auth:user', ['only' => ['store','show','update','destroy']]);
        $this->middleware('default.headers');
    }
	
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $rates = UserRate::with('services')->get();
		$response = ['data' => $rates,'code' => 200];
		return response()->json($rates,200);
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
		$userRequested = \Auth::User();
		
		$messages = UserRate::getMessages();
		$validation = UserRate::getValidations();
		
		$v = Validator::make($request->all(),$validation,$messages);					
		
		//SE VERIFICA SI ALGUN CAMPO NO ESTA CORRECTO
		if($v->fails()){	
			$response = ['error' => $v->messages(), 'code' =>  422];
			return response()->json($response,422);
		}
		
		$service_id = $request->service_id;
		$service = Service::with('branch.company')->where('id','=',$service_id)->first();				
		
		//SE VALIDA QUE EL SERVICE EXISTA
		if(!is_null($service)){
			
			$user = User::find($service->user_id);
			
			//SE VERIFICA QUE EL USER QUE HIZO LA PETICION SOLO PUEDA GUARDAR UN RATE
			if($userRequested->id == $user->id){
				
				
				
				$company = $service->branch->company;	
				
				$rate = new UserRate;
				$rate->service_id = $request->service_id;
				$rate->rate = $request->rate;
				$rate->comment = $request->comment;				
				$rate->partner_id = $company->partner_id;
				
				$rate = $rate->save();
				
				//SE VALIDA QUE EL REGISTRO SE HALLA GUARDADO
				if(!is_null($rate)){
					$response = ['data' => $rate,'code' => 200,'message' => 'Rate was registered succefully'];
					return response()->json($response,200);
				}else{
					$response = ['error' => 'It has occurred an error trying to register the rate','code' => 404];
					return response()->json($response,404);
				}
				
			}else{
				$response = ['error'   => 'Unauthorized','code' => 403];
				return response()->json($response, 403);
			}
									
		}else{
			//EN DADO CASO QUE EL ID DEL SERVICE NO SE HALLA ENCONTRADO
			$response = ['error' => 'Service does not exist','code' => 422];
			return response()->json($response,422);
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
        $rate = UserRate::find($id);
		
		if(!is_null($rate)){
			
			$response = ['data' => $rate,'code' => 200];
			return response()->json($response,200);
			
		}else{
			$response = ['error' => 'Rate does no exist','code' => 422];
			return response()->json($response,422);
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
        $messages = UserRate::getMessages();
		$validation = UserRate::getValidations();
		
		$v = Validator::make($request->all(),$validation,$messages);					
		
		//SE VERIFICA SI ALGUN CAMPO NO ESTA CORRECTO
		if($v->fails()){	
			$response = ['error' => $v->messages(), 'code' =>  422];
			return response()->json($response,422);
		}
		
		$rate = UserRate::find($id);
		
		//SE VALIDA QUE EL RATE EXISTA
		if(!is_null($rate)){
			
			//SE GUARDAN EN UN ARREGLO LOS CAMPOS QUE SE PUEDEN ACTUALIZAR Y SE IGUALAN A LOS QUE VIENEN POR LA PETICION		
			$fields = ['rate' => $request->rate,'comment' => $request->comment];
			
			$row = UserRate::where('id','=',$id)->update($fields);
			
			//SE VALIDA QUE SE HALLA ACTUALIZADO EL REGISTRO
			if($row != false){
				$response = ['data' => $rate,'code' => 200,'message' => 'Rate was updated succefully'];
				return response()->json($response,200);
			}else{
				$response = ['error' => 'It has occurred an error trying to update the rate','code' => 404];
				return response()->json($response,404);
			}
			
		}else{
			
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
        $rate = UserRate::find($id);

		if(!is_null($rate)){
			
			//SE BORRA EL RATE
			$row = $rate->delete();
			
			if($row != false){
				$response = ['code' => 200,'message' => "Rate was deleted succefully"];
				return response()->json($response,200);
			}else{
				$response = ['error' => 'It has occurred an error trying to delete the rate','code' => 404];
				return response()->json($response,404);
			}			
			
		}else{
			//EN DADO CASO QUE EL ID DEL RATE NO SE HALLA ENCONTRADO
			$response = ['error' => 'Rate does not exist','code' => 422];
			return response()->json($response,422);
		}
    }
}

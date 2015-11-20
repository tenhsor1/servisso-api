<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Company;
use App\Partner;
use Validator;
use JWTAuth;

class CompanyController extends Controller
{
	
	public function __construct(){
        $this->middleware('jwt.auth:partner', ['only' => ['show','destroy','update','store']]);
        $this->middleware('default.headers');
    }
	
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
		
		$companies = Company::with('branches')->get();
		$response = ['data' => $companies,'code' => 200];
		
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
		$partnerRequested = \Auth::User();
		
		$messages = Company::getMessages();
		$validation = Company::getValidations();
		
        $v = Validator::make($request->all(),$validation,$messages);		
		
		$response = ['error' => $v->messages(), 'code' =>  406];
		
		//SE VERIFICA SI ALGUN CAMPO NO ESTA CORRECTO
		if($v->fails()){	
			return response()->json($response,422);
		}
				
		//ID DEL ASOCIADO QUE LE PERTENECE LA COMPANY
		$partner_id = $request->partner_id;
		$partner = Partner::find($partner_id);
		
		//SE VALIDA QUE EL PARTNER EXISTA
		if(!is_null($partner)){
			
			//SE VERIFICA QUE EL PARTNER QUE HIZO LA PETICION SOLO EL SE PUEDE AGREGAR COMPANIES
			if($partnerRequested->id == $partner->id){
				
				//SE HACE UNA INSTANCIA DE COMPANY
				$company = new Company;
				$company->partner_id = $partner_id;
				$company->name = $request->name;
				$company->description = $request->description;
				$company->category_id = $request->category_id;
				
				$row = $company->save();
				
				if($row != false){
					$response = ['data' => $company,'code' => 200,'message' => 'Company was created succefully'];
					return response()->json($response,200);
				}else{
					$response = ['error' => 'It has occurred an error trying to save the company','code' => 404];
					return response()->json($response,404);
				}
			}else{
				$response = ['error'   => 'Unauthorized','code' => 403];
				return response()->json($response, 403);
			}
						
		}else{
			//EN DADO CASO QUE EL ID DE PARTNER NO SE HALLA ENCONTRADO
			$response = ['error' => 'Partner does not exist','code' => 422];
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
		$partnerRequested = \Auth::User();	
			
		$company = Company::find($id);
		
		//SE VERIFICA QUE LA COMPANY EXISTA
		if(!is_null($company)){
			
			//SE VERIFICA QUE EL PARTNER QUE HIZO LA PETICION SOLO PUEDA OBTENER INFO DE SUS COMPANIES
			if($partnerRequested->id == $company->partner_id){
				
				$response = ['code' => 200,'data' => $company];
				return response()->json($response,200);
				
			}else{
				
				$response = ['error'   => 'Unauthorized','code' => 403];
				return response()->json($response, 403);
			}			
				
		}else{
			$response = ['error' => 'Company does no exist','code' => 422];
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
		$partnerRequested = \Auth::User();
		
        $company = Company::find($id);
		
		//SE VERIFICA QUE COMPANY EXISTA
		if(!is_null($company)){
			
			//SE VERIFICA QUE EL PARTNER QUE HIZO LA PETICION SOLO PUEDA ACTUALIZAR SUS COMPANIES
			if($partnerRequested->id == $company->partner_id){
				
				$messages = Company::getMessages();
				$validation = Company::getValidations();
				
				$v = Validator::make($request->all(),$validation,$messages);	
				
				//SE VERIFICA SI ALGUN CAMPO NO ESTA CORRECTO
				if($v->fails()){
					$response = ['error' => $v->messages(),'code' => 422];
					return response()->json($response,422);
				}
				
				//SE LE COLOCAN LOS NUEVOS VALORES
				$company->name = $request->name;
				$company->description = $request->description;
				$company->category_id = $request->category_id;
				
				$row = $company->save();	
				
				if($row != false){
					$response = ['data' =>$company,'code' => 200,'message' => "Company was updated succefully"];
					return response()->json($response,200);
				}else{
					$response = ['error' => 'It has occurred an error trying to update the company','code' => 404];
					return response()->json($response,404);
				}	
				
			}else{
				$response = ['error'   => 'Unauthorized','code' => 403];
				return response()->json($response, 403);
			}						
			
		}else{
			//EN DADO CASO QUE EL ID DE COMPANY NO SE HALLA ENCONTRADO
			$response = ['error' => 'Company does not exist','code' => 422];
			return response()->json($response,422);
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
		$partnerRequested = \Auth::User();	
		
        $company = Company::find($id);
		
		//SE VERIFICA QUE LA COMPANY EXISTA
		if(!is_null($company)){
			
			//SE VERIFICA QUE EL PARTNER QUE HIZO LA PETICION SOLO PUEDA ELIMINAR SUS COMPANIES
			if($partnerRequested->id == $company->partner_id){
				
				//SE BORRA LA COMPANY
				$row = $company->delete();
				
				if($row != false){
					$response = ['code' => 200,'message' => "Company was deleted succefully"];
					return response()->json($response,200);
				}else{
					$response = ['error' => 'It has occurred an error trying to delete the company','code' => 404];
					return response()->json($response,404);
				}
				
			}else{
				
				$response = ['error'   => 'Unauthorized','code' => 403];
				return response()->json($response, 403);
			}								
			
		}else{
			//EN DADO CASO QUE EL ID DE LA COMPANY NO SE HALLA ENCONTRADO
			$response = ['error' => 'Company does not exist','code' => 422];
			return response()->json($response,422);
		}
    }
	
}

<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Company;
use App\Partner;
use Validator;

class CompanyController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
		
		$companies = Company::with('partner','branches')->get();
		return response()->json($companies->all(),200,[],JSON_PRETTY_PRINT);
		
		//PARA GENERAR LA VISTA Y HACER PRUEBAS
        //$companies = Company::all();
		//return view('company.index')->with('companies',$companies);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
		//PARA SIMULAR UN ASOCIADO EN LA SESSION
		$partner = Partner::find(1);
		$partner->key_p = base64_encode($partner->id); 
		
      //  return view('company.create')->with('partner',$partner);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
		$messages = $this->getMessages();
		$validation = $this->getValidations();
		
        $v = Validator::make($request->all(),$validation,$messages);		
		
		$response = ['error' => $v->messages(), 'code' =>  406];
		
		//SE VERIFICA SI ALGUN CAMPO NO ESTA CORRECTO
		if($v->fails()){
			//return response()->json($v->messages(),200);			
			return response()->json($response,460,[],JSON_PRETTY_PRINT);
		}
				
		//SE BUSCA AL ASOCIADO QUE LE PERTENERA LA COMPANY 
		$id = base64_decode($request->key_p);
		$partner = Partner::find($id);
		
		//SE GUARDA LA COMPANY
		$company = $partner->companies()->create($request->all());
		
		if(!is_null($company)){
			$response = ['code' => 200,'message' => 'Company was created succefully'];
			return response()->json($response,200,[],JSON_PRETTY_PRINT);
		}else{
			$response = ['error' => 'It has occurred an error trying to save the company','code' => 404];
			return response()->json($response,404,[],JSON_PRETTY_PRINT);
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
        $company = Company::find($id);
		if(!is_null($company)){
			$response = ['code' => 200,'data' => $company];
			return response()->json($response,200,[],JSON_PRETTY_PRINT);
		}else{
			$response = ['error' => 'Company does no exist','code' => 404];
			return response()->json($response,404,[],JSON_PRETTY_PRINT);
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
        $company = Company::find($id);
		if(!is_null($company)){
			//return view('company.edit')->with('company',$company);
		}else{
			$response = ['error' => 'Company does no exist','code' => 404];
			return response()->json($response,404,[],JSON_PRETTY_PRINT);
		}
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
        $company = Company::find($id);
		if(!is_null($company)){
			
			$messages = $this->getMessages();
			$validation = $this->getValidations();
			
			$v = Validator::make($request->all(),$validation,$messages);	
			
			//SE VERIFICA SI ALGUN CAMPO NO ESTA CORRECTO
			if($v->fails()){
				$response = ['error' => $v->messages(),'code' => 404];
				return response()->json($response,404,[],JSON_PRETTY_PRINT);
			}
			
			//SE ACTUALIZA COMPANY
			$rows = $company->update($request->all());
			
			if($rows > 0){
				$response = ['code' => 200,'message' => "Company was updated succefully"];
				return response()->json($response,200,[],JSON_PRETTY_PRINT);
			}else{
				$response = ['error' => 'It has occurred an error trying to update the company','code' => 404];
				return response()->json($response,404,[],JSON_PRETTY_PRINT);
			}
			
			
		}else{
			//EN DADO CASO QUE EL ID DE COMPANY NO SE HALLA ENCONTRADO
			$response = ['error' => 'Company does not exist','code' => '404'];
			return response()->json($response,404);
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
        $company = Company::find($id);
		if(!is_null($company)){
			
			//SE BORRA LA COMPANY
			$rows = $company->delete();
			
			if($rows > 0){
				$response = ['code' => 200,'message' => "Company was deleted succefully"];
				return response()->json($response,200,[],JSON_PRETTY_PRINT);
			}else{
				$response = ['error' => 'It has occurred an error trying to delete the company','code' => 404];
				return response()->json($response,404,[],JSON_PRETTY_PRINT);
			}
			
			
			
		}else{
			//EN DADO CASO QUE EL ID DE LA COMPANY NO SE HALLA ENCONTRADO
			$response = ['error' => 'Company does not exist','code' => '404'];
			return response()->json($response,404);
		}
    }
	
	/**
	* Se obtienen los mensajes de errores
	*/
	private function getMessages(){
		$messages = 
		[
			'required' => ':attribute is required',
			'mimes' => ':attribute invalid format, allow: jpeg,png,bmp',
			'max' => ':attribute length too long',
			'min' => ':attribute length too short',
		];
		
		return $messages;
	}
	
	/**
	* Se obtienen las validaciones del modelo Partner
	*/
	private function getValidations(){
		$validation = 
			[
				'name' => 'required|max:59|min:4',
				'description' => 'required|max:499|min:4',
				'category_id' => ''
			];
		
		return $validation;
	}
}

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
		$response = ['data' => $companies,'code' => 200];
		
		return response()->json($response,200);
		
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
		
       return view('company.create')->with('partner',$partner);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
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
			
			//SE HACE UNA INSTANCIA DE COMPANY
			$company = new Company;
			$company->partner_id = $partner_id;
			$company->name = $request->name;
			$company->description = $request->description;
			$company->category_id = $request->category_id;
			$company->companiescol = $request->companiescol;
			
			$row = $company->save();
			
			if($row != false){
				$response = ['data' => $company,'code' => 200,'message' => 'Company was created succefully'];
				return response()->json($response,200);
			}else{
				$response = ['error' => 'It has occurred an error trying to save the company','code' => 404];
				return response()->json($response,404);
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
			
			$messages = Company::getMessages();
			$validation = Company::getValidations();
			
			$v = Validator::make($request->all(),$validation,$messages);	
			
			//SE VERIFICA SI ALGUN CAMPO NO ESTA CORRECTO
			if($v->fails()){
				$response = ['error' => $v->messages(),'code' => 422];
				return response()->json($response,422);
			}
			
			//SE GUARDAN EN UN ARREGLO LOS CAMPOS QUE SE PUEDEN ACTUALIZAR Y SE IGUALAN A LOS QUE VIENEN POR LA PETICION		
			$fields = ['name' => $request->name,'description' => $request->description,'category_id' => $request->category_id,
			'companiescol' => $request->companiescol];
		
			//SE ACTUALIZA COMPANY
			$row = Company::where('id','=',$id)->update($fields);		
			
			if($row != false){
				$response = ['data' =>$company,'code' => 200,'message' => "Company was updated succefully"];
				return response()->json($response,200);
			}else{
				$response = ['error' => 'It has occurred an error trying to update the company','code' => 404];
				return response()->json($response,404);
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
			$row = $company->delete();
			
			if($row != false){
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
	
}

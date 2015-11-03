<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use Validator;
use App\Partner;

class PartnerController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
	
		$partners = Partner::with('companies.branches')->get();
		$response = ['data' => $partners,'code' => 200];

		return response()->json($response,200,[],JSON_PRETTY_PRINT);
		
		//PARA GENERAR LA VISTA Y HACER PRUEBAS
		//$partners = Partner::all();	
        //return view('partner.index')->with('partners',$partners);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
       // return view('partner.create');
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
        $partner = Partner::find($id);
		if(!is_null($partner)){
			$response = ['code' => 200,'data' => $partner];
			return response()->json($response,200);
		}else{
			$response = ['error' => 'Partner does no exist','code' => 422];
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
		$partner = Partner::find($id);
		
		if(!is_null($partner)){
		//	return view('partner.edit')->with('partner',$partner);
		}else{
			$response = ['error' => 'Partner does no exist','code' => 404];
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
        $partner = Partner::find($id);
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

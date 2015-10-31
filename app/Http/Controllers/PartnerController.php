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

		return response()->json($partners->all(),200,[],JSON_PRETTY_PRINT);
		
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

		$messages = $this->getMessages();
		$validation = $this->getValidations();
		
        $v = Validator::make($request->all(),$validation,$messages);		
		
		$response = ['error' => $v->messages(), 'code' =>  406];
		
		//SE VERIFICA SI ALGUN CAMPO NO ESTA CORRECTO
		if($v->fails()){
			//return response()->json($v->messages(),200);			
			return response()->json($response,460,[],JSON_PRETTY_PRINT);
		}
		
		//SE CREA PARTNER
		$partner = Partner::create($request->all());
		
		if(!is_null($partner)){
			$response = ['code' => 200,'message' => 'Partner was created succefully'];
			return response()->json($response,200,[],JSON_PRETTY_PRINT);
		}else{
			$response = ['error' => 'It has occurred an error trying to save the partner','code' => 404];
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
        $partner = Partner::find($id);
		if(!is_null($partner)){
			$response = ['code' => 200,'data' => $partner];
			return response()->json($response,200,[],JSON_PRETTY_PRINT);
		}else{
			$response = ['error' => 'Partner does no exist','code' => 404];
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
			
			$messages = $this->getMessages();
			$validation = $this->getValidations();
			
			$v = Validator::make($request->all(),$validation,$messages);	
			
			//SE VERIFICA SI ALGUN CAMPO NO ESTA CORRECTO
			if($v->fails()){
				$response = ['error' => $v->messages(),'code' => 404];
				return response()->json($response,404,[],JSON_PRETTY_PRINT);
			}
			
			//SE ACTUALIZA PARTNER
			$rows = $partner->update($request->all());
			
			if($rows > 0){
				$response = ['code' => 200,'message' => "Partner was updated succefully"];
				return response()->json($response,200,[],JSON_PRETTY_PRINT);
			}else{
				$response = ['error' => 'It has occurred an error trying to update the partner','code' => 404];
				return response()->json($response,404,[],JSON_PRETTY_PRINT);
			}
			
			
		}else{
			//EN DADO CASO QUE EL ID DEL PARTNER NO SE HALLA ENCONTRADO
			$response = ['error' => 'Partner does not exist','code' => '404'];
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
        $partner = Partner::find($id);
		if(!is_null($partner)){
			
			//SE BORRAR EL PARTNER
			$rows = $partner->delete();
			
			if($rows > 0){
				$response = ['code' => 200,'message' => "Partner was deleted succefully"];
				return response()->json($response,200,[],JSON_PRETTY_PRINT);
			}else{
				$response = ['error' => 'It has occurred an error trying to delete the partner','code' => 404];
				return response()->json($response,404,[],JSON_PRETTY_PRINT);
			}
			
			
			
		}else{
			//EN DADO CASO QUE EL ID DEL PARTNER NO SE HALLA ENCONTRADO
			$response = ['error' => 'Partner does not exist','code' => '404'];
			return response()->json($response,404);
		}
    }
	
	/**
	* Se obtienen los mensajes de errores
	*/
	private function getMessages(){
		$messages = [
		'required' => ':attribute is required',
		'email' => ':attribute has invalid format',
		'date' => ':attribute should be 10 digits',
		'mimes' => ':attribute invalid format, allow: jpeg,png,bmp',
		'digits' => ':attribute should be 10 digits',
		'max' => ':attribute length too long',
		'min' => ':attribute length too short',
		'string' => ':attribute should be characters only'
		];
		
		return $messages;
	}
	
	/**
	* Se obtienen las validaciones del modelo Partner
	*/
	private function getValidations(){
		$validation = ['email' => 'required|email|max:70|min:11',
				'password' => 'required|max:99|min:7',
				'name' => 'required|max:45|min:4',
				'lastname' => 'required|max:45|min:4',
				'birthdate' => 'max:20|digits:10',
				'phone' => 'required|digits:10|max:20|min:10',
				'address' => 'required|max:150|min:10',
				'zipcode' => 'required|max:10|min:4',
				'state' => 'required',
				'country' => 'required',
				'status' => 'required',
				'plan' => 'required'];
		
		return $validation;
	}
}

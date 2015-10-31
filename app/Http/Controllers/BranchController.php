<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Branch;
use App\Partner;
use Validator;

class BranchController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
		$branches = Branch::with('company.partner')->get();       
		return response()->json($branches->all(),200,[],JSON_PRETTY_PRINT);
		
		//PARA GENERAR LA VISTA Y HACER PRUEBAS
		//$branches = Branch::all();
		//return view('branch.index')->with('branches',$branches);
		
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
		$partner = Partner::find(1);
		
		if(!is_null($partner)){
			$partner->key_p = base64_encode($partner->id); 
			//return view('branch.create')->with('partner',$partner);
		}else{
			$response = ['error' => 'Branch does no exist','code' => 404];
			return response()->json($response,404,[],JSON_PRETTY_PRINT);
		}
		
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
				
		//SE BUSCA AL ASOCIADO QUE LE PERTENERA LA BRANCH 
		$id = base64_decode($request->key_p);
		$partner = Partner::find($id);
		
		//SE OBTIENE EL ID DE LA COMPANY QUE LE PERTENCE LA BRANCH
		$id_company = $request->key_c;
		
		//companies()->get() SE CONVIERTE EN COLLECTION
		//companies()->get()->get($id_company) DE LA COLLECTION SE OBTIENE LA COMPANY
		//companies()->get()->get($id_company)->branches() DE LA COMPANY SE OBTIENEN LAS BRANCHES Y
		//SE GUARDA LA BRANCH NUEVA
		$branch = $partner->companies()->get()->get($id_company)->branches()->create($request->all());
		
		if(!is_null($branch)){
			$response = ['code' => 200,'message' => 'Branch was created succefully'];
			return response()->json($response,200,[],JSON_PRETTY_PRINT);
		}else{
			$response = ['error' => 'It has occurred an error trying to save the branch','code' => 404];
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
        $branch = Branch::find($id);
		if(!is_null($branch)){
			$response = ['code' => 200,'data' => $branch];
			return response()->json($response,200,[],JSON_PRETTY_PRINT);
		}else{
			$response = ['error' => 'Branch does no exist','code' => 404];
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
		$branch = Branch::find($id);	
		if(!is_null($branch)){
		//	return view('branch.edit')->with('branch',$branch);
		}else{
			$response = ['error' => 'Branch does no exist','code' => 404];
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
        $messages = $this->getMessages();
		$validation = $this->getValidations();
		
		$v = Validator::make($request->all(),$validation,$messages);		
		
		$response = ['error' => $v->messages(), 'code' =>  406];
		
		//SE VERIFICA SI ALGUN CAMPO NO ESTA CORRECTO
		if($v->fails()){	
			return response()->json($response,460,[],JSON_PRETTY_PRINT);
		}
		
		//SE GUARDAN EN UN ARREGLO LOS CAMPOS QUE SE PUEDEN ACTUALIZAR Y SE IGUALAN A LOS QUE VIENEN POR LA PETICION
		$fields = ['address' => $request->address,'phone' => $request->phone,'schedule' => $request->schedule,
		'latitude' => $request->latitude, 'longitude' => $request->longitude];
		
		$rows = Branch::where('id','=',$id)->update($fields);
		
		//SI LAS ROWS AFECTADAS SON IGUAL A 1 O MAS ENTONCES SI SE GUARDO
		if($rows > 0){
			$response = ['code' => 200,'message' => 'Branch was created succefully'];
			return response()->json($response,200,[],JSON_PRETTY_PRINT);
		}else{
			$response = ['error' => 'It has occurred an error trying to update the branch','code' => 404];
			return response()->json($response,404,[],JSON_PRETTY_PRINT);
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
        $branch = Branch::find($id);

		if(!is_null($branch)){
			
			//SE BORRAR LA BRANCH
			$rows = $branch->delete();
			
			if($rows > 0){
				$response = ['code' => 200,'message' => "Branch was deleted succefully"];
				return response()->json($response,200,[],JSON_PRETTY_PRINT);
			}else{
				$response = ['error' => 'It has occurred an error trying to delete the branch','code' => 404];
				return response()->json($response,404,[],JSON_PRETTY_PRINT);
			}
			
			
		}else{
			//EN DADO CASO QUE EL ID DE LA BRANCH NO SE HALLA ENCONTRADO
			$response = ['error' => 'Branch does not exist','code' => '404'];
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
			'max' => ':attribute length too long',
			'min' => ':attribute length too short',
			'numeric' => ':attribute should be a number'
		];
		
		return $messages;
	}
	
	/**
	* Se obtienen las validaciones del modelo Branch
	*/
	private function getValidations(){
		$validation = 
			[
				'address' => 'required|max:59|min:4',
				'phone' => 'required|max:70|min:10',
				'latitude' => 'required|numeric',
				'longitude' => 'required|numeric',
				'schedule' => 'required|max:99|min:4'
			];
		
		return $validation;
	}
}

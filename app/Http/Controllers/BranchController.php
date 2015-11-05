<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Branch;
use App\Partner;
use App\Company;
use App\Tag;
use App\TagBranch;

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
		return response()->json($branches->all(),200);
		
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
		$companies = Company::all();
		return view('branch.create')->with('companies',$companies);
		
		
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {	
        $messages = Branch::getMessages();
		$validation = Branch::getValidations();
		
		$v = Validator::make($request->all(),$validation,$messages);					
		
		//SE VERIFICA SI ALGUN CAMPO NO ESTA CORRECTO
		if($v->fails()){	
			$response = ['error' => $v->messages(), 'code' =>  422];
			return response()->json($response,460);
		}
		
		//SE OBTIENE EL ID DE LA COMPANY QUE LE PERTENCE LA BRANCH
		$company_id = $request->company_id;
		$company = Company::find($company_id);
		
		if(!is_null($company)){
			
			//SE UNA INSTANCIA DE BRANCH
			$branch = new Branch;
			$branch->company_id = $company_id ;
			$branch->address = $request->address;
			$branch->phone = $request->phone;
			$branch->latitude = $request->latitude;
			$branch->longitude = $request->longitude;
			$branch->state_id = 1;
			$branch->schedule = $request->schedule;
			
			$row = $branch->save();
			
			if($row != false){
				$response = ['data' => $branch,'code' => 200,'message' => 'Branch was created succefully'];
				return response()->json($response,200);
			}else{
				$response = ['error' => 'It has occurred an error trying to save the branch','code' => 404];
				return response()->json($response,404);
			}
		}else{
			//EN DADO CASO QUE EL ID DE LA COMPANY NO SE HALLA ENCONTRADO
			$response = ['error' => 'Company does not exist','code' => 422];
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
        $branch = Branch::find($id);
		if(!is_null($branch)){
			$response = ['code' => 200,'data' => $branch];
			return response()->json($response,200);
		}else{
			$response = ['error' => 'Branch does no exist','code' => 422];
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
		$branch = Branch::find($id);	
		if(!is_null($branch)){
			return view('branch.edit')->with('branch',$branch);
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
        $messages = Branch::getMessages();
		$validation = Branch::getValidations();
		
		$v = Validator::make($request->all(),$validation,$messages);						
		
		//SE VERIFICA SI ALGUN CAMPO NO ESTA CORRECTO
		if($v->fails()){	
			$response = ['error' => $v->messages(), 'code' =>  422];
			return response()->json($response,422);
		}
		
		$branch = Branch::find($id);
		
		//SE VALIDA QUE LA BRANCH EXISTA
		if(!is_null($branch)){
			
			//SE GUARDAN EN UN ARREGLO LOS CAMPOS QUE SE PUEDEN ACTUALIZAR Y SE IGUALAN A LOS QUE VIENEN POR LA PETICION
			$fields = ['address' => $request->address,'phone' => $request->phone,'schedule' => $request->schedule,
			'latitude' => $request->latitude, 'longitude' => $request->longitude];
			
			$row = Branch::where('id','=',$id)->update($fields);
			
			//SE VALIDA QUE SE HALLA ACTUALIZADO EL REGISTRO
			if($row != false){
				$response = ['data' => $branch,'code' => 200,'message' => 'Branch was updated succefully'];
				return response()->json($response,200);
			}else{
				$response = ['error' => 'It has occurred an error trying to update the branch','code' => 404];
				return response()->json($response,404);
			}
		}else{
			//EN DADO CASO QUE EL ID DE BRANCH NO SE HALLA ENCONTRADO
			$response = ['error' => 'Branch does not exist','code' => 422];
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
        $branch = Branch::find($id);

		if(!is_null($branch)){
			
			//SE BORRAR LA BRANCH
			$rows = $branch->delete();
			
			if($rows > 0){
				$response = ['code' => 200,'message' => "Branch was deleted succefully"];
				return response()->json($response,200);
			}else{
				$response = ['error' => 'It has occurred an error trying to delete the branch','code' => 404];
				return response()->json($response,404);
			}			
			
		}else{
			//EN DADO CASO QUE EL ID DE LA BRANCH NO SE HALLA ENCONTRADO
			$response = ['error' => 'Branch does not exist','code' => 422];
			return response()->json($response,422);
		}
    }
	
	/**
	* Este método es llamado con la url: dominio/branch/{branch_id}/tag es de tipo GET y se obtiene
	* un listado de tags que le pertenecen a una branch.
	* Se usa este método dentro del controlador de Branch para respetar
	* la jerarquía, ya que un tag puede pertenecer a una branch.
	*/
	public function tags($id){
		
		$branch = Branch::find($id);
		if(!is_null($branch)){
			
			$tags = \DB::table('tags_branches')
			->join('tags','tags_branches.tag_id','=','tags.id')
			->where('branch_id','=',$id)
			->select('tags.*')
			->get();
			
			$response = ['data' => $tags,'code' => 200];
			
			return response()->json($response,200);
			
		}else{
			//EN DADO CASO QUE EL ID DE BRANCH NO SE HALLA ENCONTRADO
			$response = ['error' => 'Branch does not exist','code' => 422];
			return response()->json($response,422);
		}
	}
	
	/**
	* Este método es llamado con la url: dominio/branch/tag es de tipo POST y guarda un tag.
	* Se usa este método dentro del controlador de Branch para respetar
	* la jerarquía, ya que un tag puede pertenecer a una branch.
	*/
	public function tagStore(Request $request){
		
		$messages = TagBranch::getMessages();
		$validation = TagBranch::getValidations();
		
		$v = Validator::make($request->all(),$validation,$messages);		
		
		$response = ['error' => $v->messages(), 'code' =>  422];
		
		//SE VERIFICA SI ALGUN CAMPO NO ESTA CORRECTO
		if($v->fails()){		
			return response()->json($response,460);
		}
		
		$branch_id = $request->branch_id;
		$tag_id = $request->tag_id;
		
		$branch = Branch::find($branch_id);
		$tag = Tag::find($tag_id);
		
		//SE VERIFICA QUE LA BRANCH Y CATEGORY EXISTAN
		if(!is_null($branch) && !is_null($tag)){
			
			//SE GUARDA EL TAG QUE LE PERTENECE A LA BRANCH
			$row = \DB::table('tags_branches')->insert(
				[
					'tag_id' => $tag->id,
					'branch_id' => $branch->id
				]
			);
			
			//SI LAS ROWS AFECTADAS SON IGUAL A 1 O MAS ENTONCES SI SE GUARDO
			if($row != false){
				$response = ['code' => 200,'message' => 'Tag was created succefully'];
				return response()->json($response,200);
			}else{
				$response = ['error' => 'It has occurred an error trying to update the tag','code' => 404];
				return response()->json($response,404);
			}
			
		}else{
			//EN DADO CASO QUE EL ID DE CATEGORY NO SE HALLA ENCONTRADO
			$response = ['error' => 'Tag/Branch does not exist','code' => 422];
			return response()->json($response,422);
		}
		
	}
	
	/**
	* Este método es llamado con la url: dominio/branch/tag/{tag_id} es de tipo PUT y actualiza un tag.
	* Se usa este método dentro del controlador de Branch para respetar
	* la jerarquía, ya que un tag puede pertenecer a una branch.
	*/
	public function tagUpdate(Request $request, $id)
    {
        $messages = TagBranch::getMessages();
		$validation = TagBranch::getValidations();
		
		$v = Validator::make($request->all(),$validation,$messages);		
		
		$response = ['error' => $v->messages(), 'code' =>  422];
		
		//SE VERIFICA SI ALGUN CAMPO NO ESTA CORRECTO
		if($v->fails()){	
			return response()->json($response,460,[],JSON_PRETTY_PRINT);
		}
		
		$tag_id = $request->tag_id;
		//$tag = DB::table('tags_branches')->where('id','=',$id)->get();
		$tag = \DB::table('tags_branches')->where('id','=',$id)->first();
		
		//SE VALIDA QUE EL TAG A ACTUALIZAR EXISTA
		//if(count($tag) > 0){
		if(!is_null($tag)){
			
			//SE GUARDAN EN UN ARREGLO LOS CAMPOS QUE SE PUEDEN ACTUALIZAR Y SE IGUALAN A LOS QUE VIENEN POR LA PETICION
			$fields = ['tag_id' => $tag_id];
			
			$row = \DB::table('tags_branches')->where('id','=',$id)->update($fields);
			
			//SI LAS ROWS AFECTADAS SON IGUAL A 1 O MAS ENTONCES SI SE GUARDO
			if($row != false){
				$response = ['code' => 200,'message' => 'Tag was updated succefully'];
				return response()->json($response,200);
			}else{
				$response = ['error' => 'It has occurred an error trying to update the tag','code' => 404];
				return response()->json($response,404);
			}
			
		}else{
			//EN DADO CASO QUE EL ID DEL TAG NO SE HALLA ENCONTRADO
			$response = ['error' => 'Tag does not exist','code' => 422];
			return response()->json($response,422);
		}			
		
    }
	
	/**
	* Este método es llamado con la url: dominio/branch/{tag_id} es de tipo DELETE y elimina un tag.
	* Se usa este método dentro del controlador de Branch para respetar
	* la jerarquía, ya que un tag puede pertenecer a una branch.
	*/
	public function tagDestroy($id){
		
		//SE OBTIENE EL TAG EN FORMA DE OBJETO
		$tag = \DB::table('tags_branches')->where('id','=',$id)->first();		

		//SE VALIDA QUE EL TAG EXISTA
		if(!is_null($tag)){
			
			//SE BORRA EL TAG
	
			//$row = \DB::table('tags_branches')->where('id','=',$tag->id)->delete();
			
			$fields = ['deleted_at' => date('Y-M-d hh:mm:ss',time())];
			$row = \DB::table('tags_branches')->where('id','=',$tag->id)->update($fields);
			
			if($row != false){
				$response = ['code' => 200,'message' => "Tag was deleted succefully"];
				return response()->json($response,200);
			}else{
				$response = ['error' => 'It has occurred an error trying to delete the tag','code' => 404];
				return response()->json($response,404);
			}			
			
		}else{
			//EN DADO CASO QUE EL ID DE LA BRANCH NO SE HALLA ENCONTRADO
			$response = ['error' => 'Tag does not exist','code' => 422];
			return response()->json($response,422);
		}
		
	}
	
}

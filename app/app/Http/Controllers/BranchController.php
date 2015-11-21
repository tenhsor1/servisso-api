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
use App\Service;
use Validator;
use JWTAuth;

class BranchController extends Controller
{

	public function __construct(){
        $this->middleware('jwt.auth:partner', ['only' => ['store','show','update','destroy','tags','tagStore','tagUpdate','tagDestroy']]);
        $this->middleware('jwt.auth:partner|admin', ['only' => ['services']]);
        $this->middleware('default.headers');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
		/*$branches = Branch::with('company.partner')->get();
		return response()->json($branches->all(),200);*/

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

			$partnerRequested = \Auth::User();

			//SE VERIFICA QUE EL PARTNER QUE HIZO LA PETICION SOLO PUEDA GUARDAR BRANCHES EN SUS COMPANIES
			if($partnerRequested->id == $company->partner_id){

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
				$response = ['error'   => 'Unauthorized','code' => 403];
				return response()->json($response, 403);
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
		$partnerRequested = \Auth::User();

		//SE OBTIENE LA BRANCH SOLICITIDA JUNTO CON LA COMPANY QUE LE PERTENECE
        $branch = Branch::with('company')->where('id','=',$id)->first();

		//SE VALIDA QUE EXISTA LA BRANCH
		if(!is_null($branch)){

			//SE OBTIENE LA COMPANY DE LA BRANCH
			$company = $branch->company;

			//SE VERIFICA QUE EL PARTNER QUE HIZO LA PETICION SOLO PUEDA OBTENER INFO DE SUS BRANCHES
			if($partnerRequested->id == $company->partner_id){

				$response = ['code' => 200,'data' => $branch];
				return response()->json($response,200);

			}else{

				$response = ['error'   => 'Unauthorized','code' => 403];
				return response()->json($response, 403);
			}

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

        $messages = Branch::getMessages();
		$validation = Branch::getValidations();

		$v = Validator::make($request->all(),$validation,$messages);

		//SE VERIFICA SI ALGUN CAMPO NO ESTA CORRECTO
		if($v->fails()){
			$response = ['error' => $v->messages(), 'code' =>  422];
			return response()->json($response,422);
		}

		//SE OBTIENE LA BRANCH SOLICITIDA JUNTO CON LA COMPANY QUE LE PERTENECE
        $branch = Branch::with('company')->where('id','=',$id)->first();

		//SE VALIDA QUE LA BRANCH EXISTA
		if(!is_null($branch)){

			//SE OBTIENE LA COMPANY DE LA BRANCH
			$company = $branch->company;

			//SE VERIFICA QUE EL PARTNER QUE HIZO LA PETICION SOLO PUEDA ACTUALIZAR SUS BRANCHES
			if($partnerRequested->id == $company->partner_id){

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
				$response = ['error'   => 'Unauthorized','code' => 403];
				return response()->json($response, 403);
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
		$partnerRequested = \Auth::User();

        //SE OBTIENE LA BRANCH SOLICITIDA JUNTO CON LA COMPANY QUE LE PERTENECE
        $branch = Branch::with('company')->where('id','=',$id)->first();

		if(!is_null($branch)){

			//SE OBTIENE LA COMPANY DE LA BRANCH
			$company = $branch->company;

			//SE VERIFICA QUE EL PARTNER QUE HIZO LA PETICION SOLO PUEDA ELIMINAR SUS BRANCHES
			if($partnerRequested->id == $company->partner_id){

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
				$response = ['error'   => 'Unauthorized','code' => 403];
				return response()->json($response, 403);
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

		$partnerRequested = \Auth::User();

		//SE OBTIENE LA BRANCH SOLICITIDA JUNTO CON LA COMPANY QUE LE PERTENECE
        $branch = Branch::with('company')->where('id','=',$id)->first();

		if(!is_null($branch)){

			//SE OBTIENE LA COMPANY DE LA BRANCH
			$company = $branch->company;

			//SE VERIFICA QUE EL PARTNER QUE HIZO LA PETICION SOLO PUEDA OBTENER INFO DE SUS BRANCHES
			if($partnerRequested->id == $company->partner_id){

				$tags = \DB::table('tags_branches')
				->join('tags','tags_branches.tag_id','=','tags.id')
				->where('branch_id','=',$id)
				->select('tags.id','tags.name','tags.description')
				->get();

				$response = ['data' => $tags,'code' => 200];

				return response()->json($response,200);

			}else{
				$response = ['error'   => 'Unauthorized','code' => 403];
				return response()->json($response, 403);
			}

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

		$partnerRequested = \Auth::User();

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

		//SE OBTIENE LA BRANCH SOLICITIDA JUNTO CON LA COMPANY QUE LE PERTENECE
        $branch = Branch::with('company')->where('id','=',$branch_id)->first();

		$tag = Tag::find($tag_id);

		//SE VERIFICA QUE LA BRANCH Y CATEGORY EXISTAN
		if(!is_null($branch) && !is_null($tag)){

			//SE OBTIENE LA COMPANY DE LA BRANCH
			$company = $branch->company;

			//SE VERIFICA QUE EL PARTNER QUE HIZO LA PETICION SOLO PUEDA GUARDAR TAGS DE SUS BRANCHES
			if($partnerRequested->id == $company->partner_id){

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
				$response = ['error'   => 'Unauthorized','code' => 403];
				return response()->json($response, 403);
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
		$partnerRequested = \Auth::User();

        $messages = TagBranch::getMessages();
		$validation = TagBranch::getValidations();

		$v = Validator::make($request->all(),$validation,$messages);

		$response = ['error' => $v->messages(), 'code' =>  422];

		//SE VERIFICA SI ALGUN CAMPO NO ESTA CORRECTO
		if($v->fails()){
			return response()->json($response,460,[],JSON_PRETTY_PRINT);
		}

		$tag_id = $request->tag_id;

		//SE RELACIONA EL TAG CON LA BRANCH QUE PERTENECE Y LA BRANCH SE RELACIONA CON LA COMPANY QUE PERTENECE
		$tag = \DB::table('tags_branches')
			->join('branches','tags_branches.branch_id','=','branches.id')
			->join('companies','branches.company_id','=','companies.id')
			->where('tags_branches.id','=',$id)
			->select('companies.id AS company_id')
			->first();

		//SE VALIDA QUE EL TAG A ACTUALIZAR EXISTA
		if(!is_null($tag)){

			//SE OBTIENE LA COMPANY DEL TAG
			$company = Company::find($tag->company_id);

			//SE VERIFICA QUE EL PARTNER QUE HIZO LA PETICION SOLO PUEDA ACTUALIZAR SUS TAGS
			if($partnerRequested->id == $company->partner_id){

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
				$response = ['error'   => 'Unauthorized','code' => 403];
				return response()->json($response, 403);
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

		$partnerRequested = \Auth::User();

		//SE RELACIONA EL TAG CON LA BRANCH QUE PERTENECE Y LA BRANCH SE RELACIONA CON LA COMPANY QUE PERTENECE
		$tag = \DB::table('tags_branches')
			->join('branches','tags_branches.branch_id','=','branches.id')
			->join('companies','branches.company_id','=','companies.id')
			->where('tags_branches.id','=',$id)
			->select('companies.id AS company_id')
			->first();

		//SE VALIDA QUE EL TAG EXISTA
		if(!is_null($tag)){

			//SE OBTIENE LA COMPANY DEL TAG
			$company = Company::find($tag->company_id);

			//SE VERIFICA QUE EL PARTNER QUE HIZO LA PETICION SOLO PUEDA ELIMINAR SUS TAGS DE SUS BRANCHES
			if($partnerRequested->id == $company->partner_id){

				$fields = ['deleted_at' => date('Y-M-d hh:mm:ss',time())];
				$row = \DB::table('tags_branches')->where('id','=',$id)->update($fields);

				if($row != false){
					$response = ['code' => 200,'message' => "Tag was deleted succefully"];
					return response()->json($response,200);
				}else{
					$response = ['error' => 'It has occurred an error trying to delete the tag','code' => 404];
					return response()->json($response,404);
				}
			}else{
				$response = ['error'   => 'Unauthorized','code' => 403];
				return response()->json($response, 403);
			}

		}else{
			//EN DADO CASO QUE EL ID DE LA BRANCH NO SE HALLA ENCONTRADO
			$response = ['error' => 'Tag does not exist','code' => 422];
			return response()->json($response,422);
		}

	}

	public function services($id){
		$user = \Auth::User();
		if(!Branch::find($id)){
			$response = ['error' => 'Branch not found ','code' => 403];
			return response()->json($response, 403);
		}

		if($user->roleAuth == 'PARTNER'){
			$branch = $user->getBranch($id);
			//if the partner is not the owner of the branch, then send a 403
			if(!$branch){
				$response = ['error' => 'Unauthorized','code' => 403];
				return response()->json($response, 403);
			}
		}
		$services = Service::whereBranch($id)
                                ->with('branch')
                                ->with('userable')
                                ->with('userRate')
                                ->with('partnerRate')
                                ->get();
		return response()->json(['data' => $services], 200);
	}

}

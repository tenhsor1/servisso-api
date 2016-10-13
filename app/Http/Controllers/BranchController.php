<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Branch;
use App\User;
use App\Company;
use App\Service;
use App\Tag;
use App\TagBranch;
use Validator;
use JWTAuth;
use App\UserInvitation;

class BranchController extends Controller
{

	public function __construct(){
        $this->middleware('jwt.auth:user|admin', ['only' => ['store','update','destroy','services']]);
        $this->middleware('default.headers');
		$this->user_roles = \Config::get('app.user_roles');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {

		//SE OBTINEN LAS BRANCHES
		$branches = Branch::join('companies', 'branches.company_id', '=', 'companies.id')
                            ->join('categories', 'companies.category_id', '=', 'categories.id')
                            ->join('states', 'branches.state_id', '=', 'states.id')
                            ->category($request)
                            ->searchBy($request)
                            ->within($request)
							->betweenBy($request)
							->orderByCustom($request)
							->limit($request)
							->select('branches.id',
                                'branches.inegi',
                                'branches.email',
                                'branches.company_id',
                                'branches.phone',
                                'branches.address',
                                'branches.latitude',
                                'branches.longitude',
                                'branches.state_id',
                                'branches.schedule',
                                'branches.name',
                                'companies.user_id',
                                'companies.name AS company_name',
                                'companies.description',
                                'companies.category_id',
                                'categories.name AS category_name',
                                'companies.image',
                                'companies.thumbnail')
                            ->get();

		$count = $branches->count();

		//SE ITERA SOBRE LAS BRANCHES PARA AGREGARLE LOS TAGS Y DARLE FORMA AL JSON

        foreach($branches as $branch){

			$tags = \DB::table('tags_branches')
			->join('tags','tags_branches.tag_id','=','tags.id')
			->where('tags_branches.branch_id','=',$branch->id)
			->select('tags.name','tags.description')
			->get();

			$branch->tags = $tags;

			$verifications = \DB::table('branch_verification AS bv')
			->where('bv.branch_id','=',$branch->id)
			->select('bv.verification_type','bv.description','bv.id')
			->get();

			$branch->verifications = $verifications;

		}

		$response = ['code' => 200,'count' => $count,'data' => $branches];
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

        $messages = Branch::getMessages();
		$rules = Branch::getRules();

		$v = Validator::make($request->all(),$rules,$messages);

		//SE VERIFICA SI ALGUN CAMPO NO ESTA CORRECTO
		if($v->fails()){
			$response = ['error' => $v->errors(), 'message' => 'Bad request', 'code' =>  400];
			return response()->json($response,400);
		}

		//SE OBTIENE EL ID DE LA COMPANY QUE LE PERTENCE LA BRANCH
		$company_id = $request->company_id;
		$company = Company::with('branches')->find($company_id);

		if(!is_null($company)){

			$userRequested = \Auth::User();
			$role = $userRequested->authRole;

			//SE VALIDA QUE EL USUARIO NO TENGA BRANCHES O TENGA HABILITADO LA CREACION DE MULTIPLES COMPAÑIAS/SUCURSALES
			//PARA PODER GUARDAR UNA NUEVA
			if(($userRequested->enabled_companies == \Config::get('app.NO_ENABLED_COMPANIES') &&
				$company->branches->count() == 0) || $userRequested->enabled_companies == \Config::get('app.ENABLED_COMPANIES')){

				//SE VERIFICA QUE EL USER QUE HIZO LA PETICION SOLO PUEDA GUARDAR BRANCHES EN SUS COMPANIES
				if(($userRequested->id == $company->user_id) || $role == 'ADMIN'){

					//Si se detecta un codigo de invitacion entonces de borra el codigo
					if($request->code)
						$this::clearCode($request->code,$company->user_id);

					//SE UNA INSTANCIA DE BRANCH
					$branch = new Branch;
					$branch->company_id = $company_id;
					$branch->address = $request->address;
					$branch->phone = $request->phone;
					$branch->latitude = $request->latitude;
					$branch->longitude = $request->longitude;
					$branch->state_id = $request->state_id;
					$branch->schedule = $request->schedule;
					$branch->name = $request->name;
					$branch->geom = [$request->longitude, $request->latitude];

					$branch->save();

					//SE GUARDAN LOS TAGS QUE YA EXISTEN EN LA DB EN LA BRANCH
					$this->saveTag($request->tag,$branch);

					//SE GUARDAN LOS NUEVOS TAGS CREADOS POR EL USER
					$this->newTag($request->tag_new,$company->category_id,$branch);

					//SE VALIDA QUE LA BRANCH SE HALLA GUARDADO
					if($branch != false){

						//SE OBTINEN TODOS LOS TAGS DE LA BRANCH CREADA PARA UNIRLA AL JSON
						$tags = \DB::table('tags_branches')
						->join('tags','tags_branches.tag_id','=','tags.id')
						->where('branch_id','=',$branch->id)
						->select('tags.id','tags.name','tags.description')
						->get();

						$branch->tags = $tags;

						$response = ['data' => $branch,'code' => 200,'message' => 'Branch was created succefully'];
						return response()->json($response,200);
					}else{
						$response = ['error' => 'It has occurred an error trying to save the branch','code' => 500];
						return response()->json($response,500);
					}
				}else{
					$response = ['error'   => 'Unauthorized2','code' => 403];
					return response()->json($response, 403);
				}
			}else{
				$response = ['error'   => 'Unauthorized1','code' => 403];
				return response()->json($response, 403);
			}

		}else{
			//EN DADO CASO QUE EL ID DE LA COMPANY NO SE HALLA ENCONTRADO
			$response = ['error' => 'Company does not exist','code' => 422];
			return response()->json($response,422);
		}

    }

	/*
	* Borra el código de invitación utilizado para guardar una branch y
	* actualiza el id del usuario que usó el código
	*/
	private function clearCode($code,$user_id){
		$invitation = UserInvitation::where('code','=',$code)->get()->first();
		$invitation->to_user_id = $user_id;
		$invitation->save();
		$invitation->delete();
	}

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
		//SE OBTIENE LA BRANCH SOLICITIDA JUNTO CON LA COMPANY QUE LE PERTENECE
        $branch = Branch::with('company')
                        ->with('state')
                        ->with('state.country')
						->with('verifications')
                        ->where('id','=',$id)
                        ->first();

		//SE VALIDA QUE EXISTA LA BRANCH
		if(!is_null($branch)){

			//SE OBTINEN TODOS LOS TAGS DE LA BRANCH CREADA PARA UNIRLA AL JSON
			$tags = \DB::table('tags_branches')
				->join('tags','tags_branches.tag_id','=','tags.id')
				->where('branch_id','=',$branch->id)
				->select('tags.id','tags.name','tags.description')
				->get();

			$branch->tags = $tags;

			$response = ['code' => 200,'data' => $branch];
			return response()->json($response,200);

		}else{
			$response = ['error' => 'Branch does not exist','code' => 404];
			return response()->json($response,404);
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
		$userRequested = \Auth::User();

        $messages = Branch::getMessages();
		// $validation = Branch::getValidations();
		$rules = Branch::getRules();


		$v = Validator::make($request->all(),$rules,$messages);

		//SE VERIFICA SI ALGUN CAMPO NO ESTA CORRECTO
		if($v->fails()){
			$response = ['error' => 'Bad Request', 'data' => $v->messages(), 'code' =>  400];
			return response()->json($response, 400);
		}

		//SE OBTIENE LA BRANCH SOLICITIDA JUNTO CON LA COMPANY QUE LE PERTENECE
        $branch = Branch::with('company')->where('id','=',$id)->first();

		//SE VALIDA QUE LA BRANCH EXISTA
		if(!is_null($branch)){

			//SE OBTIENE LA COMPANY DE LA BRANCH
			$company = $branch->company;

			//SE VERIFICA QUE EL USER QUE HIZO LA PETICION SOLO PUEDA ACTUALIZAR SUS BRANCHES
			if($userRequested->id == $company->user_id || $userRequested->roleAuth  == "ADMIN"){

				//SE LE COLOCAN LOS NUEVOS VALORES
				$branch->address = $request->address;
				$branch->phone = $request->phone;
				$branch->latitude = $request->latitude;
				$branch->longitude = $request->longitude;
				$branch->state_id = $request->state_id;
				$branch->schedule = $request->schedule;
				$branch->name = $request->name;
				$branch->role_id = $userRequested->id;
                $branch->geom = [$request->longitude, $request->latitude];
				$branch->role = $this->user_roles[$userRequested->roleAuth];

				$branch->save();

				\DB::table('tags_branches')->where('branch_id', '=', $id)->delete();

				//SE GUARDAN LOS TAGS QUE YA EXISTEN EN LA DB EN LA BRANCH
				$this->saveTag($request->tag,$branch);

				//SE GUARDAN LOS NUEVOS TAGS CREADOS POR EL USER
				$this->newTag($request->tag_new,$company->category_id,$branch);

				//SE VALIDA QUE SE HALLA ACTUALIZADO EL REGISTRO
				if($branch != false){

					//SE OBTINEN TODOS LOS TAGS DE LA BRANCH ACTUALIZADA PARA UNIRLA AL JSON
					$tags = \DB::table('tags_branches')
					->join('tags','tags_branches.tag_id','=','tags.id')
					->where('branch_id','=',$branch->id)
					->select('tags.id','tags.name','tags.description')
					->get();

					$branch->tags = $tags;

					$response = ['data' => $branch,'code' => 200,'message' => 'Branch was updated succefully'];
					return response()->json($response,200);
				}else{
					$response = ['error' => 'It has occurred an error trying to update the branch','code' => 500];
					return response()->json($response,500);
				}

			}else{
				$response = ['error'   => 'Unauthorized','code' => 403];
				return response()->json($response, 403);
			}

		}else{
			//EN DADO CASO QUE EL ID DE BRANCH NO SE HALLA ENCONTRADO
			$response = ['error' => 'Branch does not exist','code' => 404];
			return response()->json($response, 404);
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

		$userRequested = \Auth::User();

        //SE OBTIENE LA BRANCH SOLICITIDA JUNTO CON LA COMPANY QUE LE PERTENECE
        $branch = Branch::with('company')->where('id','=',$id)->first();

		if(!is_null($branch)){

			//SE OBTIENE LA COMPANY DE LA BRANCH
			$company = $branch->company;

			//SE VALIDA QUE EL USUARIO TENGA HABILITADO LA CREACION DE MULTIPLES COMPAÑIAS/SUCURSALES
			//PARA PODER ELIMINAR UNA SUCURSAL
			if($userRequested->enabled_companies == \Config::get('app.ENABLED_COMPANIES')){

				//SE VERIFICA QUE EL USER QUE HIZO LA PETICION SOLO PUEDA ELIMINAR SUS BRANCHES
				if($userRequested->id == $company->user_id || $userRequested->roleAuth == "ADMIN"){

					$branch->role_id = $userRequested->id;
					$branch->role = $this->user_roles[$userRequested->roleAuth];
					$branch->save();

					//SE BORRAR LA BRANCH
					$rows = $branch->delete();

					//SE ELIMINAN TODAS LAS TAG QUE LE PERTENECEN A LA BRANCH
					/*$timestamp = time()+date('Z');
					$date = date('Y-m-d H:i:s',$timestamp);
					\DB::update("UPDATE FROM tags_branches SET deteted_at = NOW WHERE branch_id = ".$branch->id." ");	*/

					if($rows > 0){
						$response = ['code' => 200,'message' => "Branch was deleted succefully"];
						return response()->json($response,200);
					}else{
						$response = ['error' => 'It has occurred an error trying to delete the branch','code' => 500];
						return response()->json($response,500);
					}
				}else{
					$response = ['error'   => 'Unauthorized','code' => 403];
					return response()->json($response, 403);
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
	* Guarda tags ya existentes de una branch.
	*  Este método se usa tanto para update y store de una branch
	*/
	private function saveTag($array,$branch){

		//SE VALIDA QUE EL ARRAY TAG EXISTE EN EL JSON
		if($array != null){

			//SE VALIDA QUE EL ARRAY TAG TENGA AL MENOS UN REGISTRO
			$tags = array_filter($array);
			if(!empty($tags)){

				//SE ELIMINAN TODAS LAS TAG QUE LE PERTENECEN A LA BRANCH
				\DB::delete('DELETE FROM tags_branches WHERE branch_id = '.$branch->id.' ');

				for($i = 0;$i < count($tags);$i++){
					$tag = (object) $tags[$i];

					$tag_verification = Tag::find($tag->tag_id);

					//Si el tag existe entonces se guarda en la db
					if($tag_verification){
						$row = \DB::table('tags_branches')->insert(
								[
											'tag_id' => $tag->tag_id,
											'branch_id' => $branch->id,
											'created_at' => date('Y-m-d h:i:s',time()),
											'updated_at' => date('Y-m-d h:i:s',time())
								]
							);

						//SE VALIDA QUE EL TAG SE GUARDO CORRECTAMENTE
						if($row != true){
							$response = ['error' => 'It has occurred an error trying to save tags','code' => 500];
							return response()->json($response,500);
						}
					}
				}
			}
		}
	}

	/**
	* Guarda tags nuevos creados por el user
	*/
	private function newTag($array,$category,$branch){

		//SE VALIDA QUE EL ARRAY TAG NEW EXISTA EN EL JSON
		if($array != null){

			//SE VALIDA QUE EL ARRAY TAG NEW TENGA AL MENOS UN REGISTRO
			$tags_new = array_filter($array);

			if(!empty($tags_new)){
					//SE GUARDAN NUEVOS TAGS CREADOS POR EL USER
				for($i = 0;$i < count($tags_new);$i++){
					$tag_new = (object) $tags_new[$i];

					$tag = new Tag;
					$tag->name = $tag_new->name;
					$tag->description = $tag_new->description;
					$tag->category_id = $category;

					$tag->save();

					$row = \DB::table('tags_branches')->insert(
							[
								'tag_id' => $tag->id,
								'branch_id' => $branch->id,
								'created_at' => date('Y-m-d H:i:s',time()),
								'updated_at' => date('Y-m-d H:i:s',time())
							]
						);

					//SE VALIDA QUE SE HALLA GUARDADO CORRECTAMENTE EL NUEVO TAG
					/*if($tag != null){
						$response = ['error' => 'It has occurred an error trying to save the tag','code' => 500];
						return response()->json($response,500);
					}*/
				}
			}
		}
	}

	public function services($id){
		$user = \Auth::User();
		if(!Branch::find($id)){
			$response = ['error' => 'Branch not found ','code' => 403];
			return response()->json($response, 403);
		}
		if($user->roleAuth == 'USER'){
			$branch = $user->getBranch($id);
			//if the user is not the owner of the branch, then send a 403
			if(!$branch){
				$response = ['error' => 'Unauthorized','code' => 403];
				return response()->json($response, 403);
			}
		}
		$services = Service::whereBranch($id)
                                ->with('userable')
                                ->with('branch')
                                ->get();
		return response()->json(['data' => $services], 200);
	}
}


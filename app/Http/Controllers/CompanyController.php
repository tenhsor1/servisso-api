<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Company;
use App\User;
use Validator;
use JWTAuth;
use App\Extensions\utils;
class CompanyController extends Controller
{

	public function __construct(){
        $this->middleware('jwt.auth:user|admin', ['only' => ['destroy','update','store','image']]);
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

		//$companies = Company::with('branches')->get();
		$companies = Company::with('branches')
							->searchBy($request)
							->betweenBy($request)
							->orderByCustom($request)
							->limit($request)
							->get();
		$count = $companies->count();
		$response = ['count' => $count,'code' => 200,'data' => $companies];
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
		$userRequested = \Auth::User();

		$messages = Company::getMessages();
		$validation = Company::getValidations();

        $v = Validator::make($request->all(),$validation,$messages);

		$response = ['error' => 'Bad Request', 'data' => $v->messages(), 'code' =>  406];

		//SE VERIFICA SI ALGUN CAMPO NO ESTA CORRECTO
		if($v->fails()){
			return response()->json($response,422);
		}

		//ID DEL ASOCIADO QUE LE PERTENECE LA COMPANY
		$user_id = $request->user_id;
		$user = User::find($user_id);

		//SE VALIDA QUE EL USER EXISTA
		if(!is_null($user)){

			//SE VERIFICA QUE EL USER QUE HIZO LA PETICION SOLO EL SE PUEDE AGREGAR COMPANIES
			if($userRequested->id == $user->id){

				//SE HACE UNA INSTANCIA DE COMPANY
				$company = new Company;
				$company->user_id = $user_id;
				$company->name = $request->name;
				$company->description = $request->description;
				$company->category_id = $request->category_id;
                $company->web = $request->web;

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
			//EN DADO CASO QUE EL ID DE USER NO SE HALLA ENCONTRADO
			$response = ['error' => 'User does not exist','code' => 422];
			return response()->json($response,422);
		}

    }


	 public function image(Request $request, $id)
    {
		$userRequested = \Auth::User();
		$company = Company::find($id);
		//SE VALIDA QUE EL USUARIO SEA DE TIPO USER O ADMIN
        if($userRequested->roleAuth  == "USER" && $userRequested->id == $company->user_id || $userRequested->roleAuth  == "ADMIN"){
			 if(!is_null($company)){
				$ext = $request->file('image')->getClientOriginalExtension();
				// Se verifica si es un formato de imagen permitido
				if($ext !='jpg' && $ext !='jpeg' && $ext !='bmp' && $ext !='png'){
					$response = ['ext' => $ext, 'error' => "Only upload images with format jpg, jpeg, bmp and png", 'code' =>  406];
					return response()->json($response,422);
				}
				// StorageImage($ImageName,$reques "file", "RuteImage" '/public/',"RuteImageThumb" '/public/')
				//SE ENVIA EL ID DE LAIMAGEN PARA MODIFICAR EL NOMBRE Y EL ARCHIVO PARA MOVERLO (RETORNA LAS RUTAS DE LA IMAGENES)
				$img = utils::StorageImage($id,$request);
				//SE LE COLOCAN EL NOMBRE DE LA IMAGEN
				$company->image = $img['image'];
				$company->thumbnail = $img['thumbnail'];
				$company->save();

				if($company != false){
					$response = ['code' => 200,'message' => 'Image was save succefully'];
					return response()->json($response,200);
				}else{
					$response = ['error' => 'It has occurred an error trying to update the company','code' => 500];
					return response()->json($response,500);
				}

			}else{
				//EN DADO CASO QUE EL ID DE COMPANY NO SE HALLA ENCONTRADO
				$response = ['error' => 'Company does not exist','code' => 422];
				return response()->json($response,422);
			}
		}else{
            $errorJSON = ['error'   => 'Unauthorized'
                , 'code' => 403];
            return response()->json($errorJSON, 403);
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
		$userRequested = \Auth::User();

        $company = Company::with('category')
                        ->with('user')
                        ->with('branches')
                        ->with('branches.state')
                        ->with('branches.state.country')
                        ->where('id','=',$id)
                        ->first();

		//SE VERIFICA QUE LA COMPANY EXISTA
		if(!is_null($company)){

			$response = ['code' => 200,'data' => $company];
			return response()->json($response,200);

		}else{
			$response = ['error' => 'Company does no exist','code' => 404];
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

        $company = Company::find($id);

		//SE VERIFICA QUE COMPANY EXISTA
		if(!is_null($company)){

			//SE VERIFICA QUE EL USER QUE HIZO LA PETICION SOLO PUEDA ACTUALIZAR SUS COMPANIES
			if($userRequested->id == $company->user_id || $userRequested->roleAuth  == "ADMIN"){

				$messages = Company::getMessages();
				$validation = Company::getValidations();

				$v = Validator::make($request->all(),$validation,$messages);

				//SE VERIFICA SI ALGUN CAMPO NO ESTA CORRECTO
				if($v->fails()){
					$response = ['error' => 'Bad Request', 'data' => $v->messages(),'code' => 422];
					return response()->json($response,422);
				}

				//SE LE COLOCAN LOS NUEVOS VALORES
				$company->name = $request->name;
				$company->description = $request->description;
				$company->category_id = $request->category_id;
                $company->web = $request->web;
				$company->role_id = $userRequested->id;
				$company->role = $this->user_roles[$userRequested->roleAuth];

				$company->save();

				if($company != false){
					$response = ['data' =>$company,'code' => 200,'message' => "Company was updated succefully"];
					return response()->json($response,200);
				}else{
					$response = ['error' => 'It has occurred an error trying to update the company','code' => 500];
					return response()->json($response,500);
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
		$userRequested = \Auth::User();

        $company = Company::find($id);

		//SE VERIFICA QUE LA COMPANY EXISTA
		if(!is_null($company)){

			//SE VERIFICA QUE EL USER QUE HIZO LA PETICION SOLO PUEDA ELIMINAR SUS COMPANIES
			if($userRequested->id == $company->user_id || $userRequested->roleAuth  == "ADMIN"){

				$company->role_id = $userRequested->id;
				$company->role = $this->user_roles[$userRequested->roleAuth];
				$company->save();

				//SE BORRA LA COMPANY
				$row = $company->delete();

				if($row != false){
					$response = ['code' => 200,'message' => "Company was deleted succefully"];
					return response()->json($response,200);
				}else{
					$response = ['error' => 'It has occurred an error trying to delete the company','code' => 500];
					return response()->json($response,500);
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

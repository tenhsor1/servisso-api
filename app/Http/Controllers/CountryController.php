<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Country;
use Validator;
use JWTAuth;
class CountryController extends Controller
{
	 public function __construct(){
        $this->middleware('jwt.auth:admin|partner|user', ['except' => ['update','show','store','index','destroy']]);
        $this->middleware('default.headers');
		$this->UserRoles = \Config::get('app.user_roles');
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
	// \DB::connection()->enableQueryLog();
       $adminRequested = \Auth::User();
            $country = Country::searchBy($request)
						->betweenBy($request)
						->orderByCustom($request)
						->limit($request)
						->get();
	// $query = \DB::getQueryLog();
			$count = $country->count();
			 if(!is_null($country)){
                $response = ['code' => 200,'Count' => $count,'data' => $country];
                return response()->json($response,200);
            }else{
                $response = ['error' => 'Countries are empty','code' => 404];
                return response()->json($response,404);
            }

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
       $adminRequested = \Auth::User();
		//SE VALIDA QUE EL USUARIO SEA DE TIPO ADMIN Y QUE EL ID DEL ADMIN LE PERTENEZCA A QUIEN CREA LA NOTICIA
        // if($adminRequested->roleAuth  == "ADMIN"){
        $messages = Country::getMessages();
        $validation = Country::getValidations();
        $v = Validator::make($request->all(),$validation,$messages);
        //SE VERIFICA SI ALGUN CAMPO NO ESTA CORRECTO
        if($v->fails()){
            $response = ['error' => $v->messages(), 'code' =>  460];
            return response()->json($response,460);
        }

            $country = new Country;
            $country->country = $request->name;
			$country->role_id = $adminRequested->id;//id de quien modifico
            $country->role = $this->UserRoles[$adminRequested->roleAuth];//rol de quien modifico
            $row= $country->save();

        if($row != false){
            $response = ['code' => 200,'message' => 'Country was created succefully'];
            return response()->json($response,200);
        }else{
            $response = ['error' => 'It has occurred an error trying to save the country','code' => 404];
            return response()->json($response,404);
        }
        // }else{
            // $errorJSON = ['error'   => 'Unauthorized'
                // , 'code' => 403];
            // return response()->json($errorJSON, 403);
        // }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
       $country = Country::find($id);
        if(!is_null($country)){
            $response = ['code' => 200,'data' => $country];
            return response()->json($response,200);
        }else{
            $response = ['error' => 'Country does no exist','code' => 404];
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
       $country = Country::find($id);

        if(!is_null($country)){

            $adminRequested = \Auth::User();//quien hizo la peticion

            // if($adminRequested->roleAuth  == "ADMIN"){ //se valida quien mando la peticion le pertenecen sus datos

                $messages = Country::getMessages();
                $validation = Country::getValidations();
                $v = Validator::make($request->all(),$validation,$messages);
                //SE VERIFICA SI ALGUN CAMPO NO ESTA CORRECTO
                if($v->fails()){
                    $response = ['error' => $v->messages(),'code' => 422];
                    return response()->json($response,404);
                }


                $country->country = $request->name;
                $country->role_id = $adminRequested->id;//id de quien modifico
                $country->role = $this->UserRoles[$adminRequested->roleAuth];//rol de quien modifico
                $row = $country->save();
                if($row != false){
                    $response = ['code' => 200,'message' => 'Cuntry was update succefully'];
					return response()->json($response,200);
                }else{
                    $response = ['error' => 'It has occurred an error trying to update the country','code' => 404];
                    return response()->json($response,404);
                }


            // }else{
                //EN DADO CASO QUE EL ID DE NO SEA UN ADMINISTRADOR
                // $response = ['error' => 'Unauthorized','code' => 403];
                // return response()->json($response,403);
            // }

        }else{
            //EN DADO CASO QUE EL ID DEL COUNTRY NO SE HALLA ENCONTRADO
            $response = ['error' => 'News does not exist','code' => '404'];
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
         $country = Country::find($id);
        if(!is_null($country)){
            $adminRequested = \Auth::User();//quien hizo la peticion
			 // if($adminRequested->roleAuth  == "ADMIN"){
				$country->role_id = $adminRequested->id;//id de quien modifico
                $country->role = $this->UserRoles[$adminRequested->roleAuth];//rol de quien modifico
                $country->save();
                $rows = $country->delete();
				if($rows > 0){
                    $response = ['code' => 200,'message' => "Country was deleted succefully"];
                    return response()->json($response,200);
                }else{
                    $response = ['error' => 'It has occurred an error trying to delete the country','code' => 404];
                    return response()->json($response,404);
                }
            // }else{
                //EN DADO CASO QUE EL ID DE NEWS NO LE PERTENEZCA
                // $response = ['error' => 'Unauthorized','code' => 403];
                // return response()->json($response,403);
            // }
        }else{
            //EN DADO CASO QUE EL ID DEL COUNTRY NO SE HALLA ENCONTRADO
            $response = ['error' => 'Contry does not exist','code' => '404'];
            return response()->json($response,404);
        }
    }
}

<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\Mailers\AppMailer;
use App\Http\Controllers\Controller;
use Validator;
use App\Partner;
use JWTAuth;

class PartnerController extends Controller
{

	public function __construct(){
        $this->middleware('jwt.auth:partner|admin', ['only' => ['show','destroy','update']]);
        $this->middleware('default.headers');
		$this->user_roles = \Config::get('app.user_roles');
        $this->mailer = new AppMailer();
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
		//\DB::connection()->enableQueryLog();
		$partners = Partner::searchBy($request)
							->betweenBy($request)
							->orderByCustom($request)
							->limit($request)
							->get();
		//$query = \DB::getQueryLog();

		$count = $partners->count();

		$response = ['code' => 200,'count' => $count,'data' => $partners];
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

		//SE CREA PARTNER
		$save = $partner->save();

		//TOKEN ES CREADO
		$extraClaims = ['role'=>'PARTNER'];
        $token = JWTAuth::fromUser($partner,$extraClaims);
        $reflector = new \ReflectionClass('JWTAuth');
        $partner->access = $token;

		if($save != false){
            $this->mailer->sendVerificationEmail($partner);
            $response = ['data' => $partner,'code' => 200,'message' => 'Partner was created succefully'];
			return response()->json($response,200);
		}else{
			$response = ['error' => 'It has occurred an error trying to save the partner','code' => 404];
			return response()->json($response,404);
		}
        $response = ['code' => 200,'message' => 'Partner was created succefully'];
        return response()->json($response,200);
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

		//SE VERIFICA QUE EL PARTNER QUE HIZO LA PETICION SOLO PUEDA OBTENER INFO DE EL MISMO
		if($userRequested->id == $id || $userRequested->roleAuth  == "ADMIN"){

			$partner = Partner::find($id);

			//SE VERIFICA QUE EL PARTNER EXISTA
			if(!is_null($partner)){
				$response = ['code' => 200,'data' => $partner];
				return response()->json($response,200);
			}else{
				$response = ['error' => 'Partner does no exist','code' => 422];
				return response()->json($response,422);
			}

		}else{
            $response = ['error'   => 'Unauthorized','code' => 403];
            return response()->json($response, 403);
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

		//SE VERIFICA QUE EL PARTNER QUE HIZO LA PETICION SOLO PUEDA ACTUALIZARSE EL MISMO
		if($userRequested->id == $id || $userRequested->roleAuth  == "ADMIN"){

			$partner = Partner::find($id);

			//SE VERIFICA QUE EL PARTNER EXISTA
			if(!is_null($partner)){

				$messages = Partner::getMessages();
				$validation = Partner::getValidations();

				$v = Validator::make($request->all(),$validation,$messages);

				//SE VERIFICA SI ALGUN CAMPO NO ESTA CORRECTO
				if($v->fails()){
					$response = ['error' => $v->messages(),'code' => 404];
					return response()->json($response,404);
				}

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
				$partner->role_id = $userRequested->id;
				$partner->role = $this->user_roles[$userRequested->roleAuth];
				$partner->save();

				if($partner != false){
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

		}else{
            $response = ['error'   => 'Unauthorized','code' => 403];
            return response()->json($response, 403);
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

		if($userRequested->roleAuth  == "ADMIN"){

			$partner = Partner::find($id);
			if(!is_null($partner)){

				$partner->role_id = $userRequested->id;
				$partner->role = $this->user_roles[$userRequested->roleAuth];
				$partner->save();

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
		}else{
			$response = ['error'   => 'Unauthorized','code' => 403];
            return response()->json($response, 403);
		}
	}

    public function confirm(Request $request){
        $validation = ['code' => 'string|required|min:30'];
        $messages = Partner::getMessages();

        $v = Validator::make($request->all(),$validation,$messages);
        if($v->fails()){
            $response = ['error' => $v->messages(),'code' => 404];
            return response()->json($response,404);
        }
        $partner = Partner::where('token', '=', $request->code)->first();
        if($partner){
            $partner->confirmed = true;
            $partner->token = null;
            $save = $partner->save();
            if($save){
                $response = ['code' => 200
                                ,'message' => "Email confirmed correctly"
                                ,'data' => $partner];
                            return response()->json($response,200);
            }else{
                $response = ['code' => 500
                        ,'error' => "Something happened when trying to confirm the email"];
                        return response()->json($response,500);
            }
        }else{
            $response = ['code' => 403
                        ,'error' => "User with code validation not found"];
                        return response()->json($response,403);
        }
    }
}

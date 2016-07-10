<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\Mailers\AppMailer;
use App\Http\Controllers\Controller;
use App\User;
use App\UserSocial;
use App\Company;
use JWTAuth;
use Validator;
use App\Extensions\Utils;

class UserController extends Controller
{
    public function __construct(){
        $this->middleware('jwt.auth:user|admin', ['except' => ['store', 'confirm','predict','storeSearched','updateSearched']]);
        $this->middleware('default.headers');
        $this->user_roles = \Config::get('app.user_roles');
        $this->mailer = new AppMailer();
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return 'index';
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\UserStoreRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {

		$valuesProvider = false;
        if($request->input('val', null)){
            //if we are trying to create a user using a social provider, then validate that
            //the social information passed is correctly, if not, return an error
            $valuesProvider = $this->getInfoFromProvider($request);
            if(array_key_exists('code', $valuesProvider)){
                return response()->json($valuesProvider['response'], $valuesProvider['code']);
            }
            $request['email'] = $valuesProvider['email'];
        }
        $rules = User::getRules();
		$messages = User::getMessages();

		$validator = Validator::make($request->all(),$rules,$messages);

		if($validator->fails()){
			$response = ['error' => $validator->errors(),'message' => 'Bad request','code' => 400];
			return response()->json($response,400);
		}

        $validCaptcha = Utils::validateCaptcha($request->input('captcha'), $request->ip());
        if(!$validCaptcha){
            $response = ['error' => ['captcha' => ['El captcha no es válido']],
                        'message' => 'Bad request','code' => 400];
            return response()->json($response,400);
        }

		$fields = \Input::except('code');

        $newUser = User::create($fields);

        if($newUser){
            if($valuesProvider){
                //if the creation of the user is based on a social provider, then save a
                //record on user_socials using loginSocial method
                $token = $newUser->loginSocial($valuesProvider);
                if($token){
                    $newUser->confirmed = true;
                    $newUser->save();
                    $newUser->access = $token;
                }else{
                    $response = ['error' => 'Un error inesperado sucedio'
                                ,'code' => 500
                                ,'data' => []];
                    return response()->json($response,500);
                }
            }else{
                //if the creation is based on email, then generate the token directly
                $extraClaims = ['role'=>'USER'];
                $token = JWTAuth::fromUser($newUser,$extraClaims);
                $reflector = new \ReflectionClass('JWTAuth');
                $newUser->access = $token;
                $this->mailer->sendVerificationEmail($newUser);
            }

			//Si el request tiene el input code significa que el usuario esta registrando una compañia que es de la inegi.
			if($request->code){
				$company_id = \Crypt::decrypt($request->code);
				$company = Company::find($company_id);
				$branch = $company->branches[0];

				//Siempre y cuando la branch de la compañia(inegi) sea true, significa que no ha sido tomada
				if($branch->inegi){
					$company->user_id = $newUser->id;
					$branch->inegi = false;
					$branch->save();
					$company->save();
				}
			}

            $response = ['data' => $newUser
                        ,'code' => 200
                        ,'message' => 'User was created succefully'];
            return response()->json($response,200);
        }
        $response = ['error' => 'It has occurred an error trying to save the user'
                    ,'code' => 404];
        return response()->json($response,500);
    }

    private function getInfoFromProvider(Request $request){
        try{
            $values = \Crypt::decrypt($request->input('val'));
        }catch(DecryptException $e){
            $data = ['val' => ["La información no es correcta"]];
            $response = ['data' => $data, 'error' => 'Bad request', 'code' => 403];
            return ['response' => $response, 'code' => 403];
        }

        $rules = UserSocial::getRules();
        $messages = UserSocial::getMessages();

        $validator = Validator::make($values,$rules,$messages);

        if($validator->fails()){
            $response = ['data' => $validator->errors(),'error' => 'Bad request','code' => 400];
            return ['response' => $response, 'code' => 400];
        }
        return $values;
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
        //check if the user who requested the resource is the same
        //as the resource been requested
        if($userRequested->id == $id){
            return response()->json(['data'=>$userRequested], 200);
        }else{
            $errorJSON = ['error'   => 'Unauthorized'
                            , 'code' => 403];
            return response()->json($errorJSON, 403);
        }
    }


    public function companies(Request $request, $userId)
    {
        $userRequested = \Auth::User();
        if($userRequested->roleAuth == 'USER'){
            if($userRequested->id != $userId){
                $errorJSON = ['error'   => 'Unauthorized'
                            , 'code' => 403];
                return response()->json($errorJSON, 403);
            }
        }

        $companies = Company::with('branches')
                            ->where('user_id', $userId)
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
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Requests\UserUpdateRequest $request, $id)
    {
        $userRequested = \Auth::User();
        if($userRequested->id == $id){
            $attributes = ['name'
                            , 'email'
                            , 'lastname'
                            , 'phone'
                            , 'address'
                            , 'zipcode'
                            , 'state_id'
                            , 'country_id'
                        ];
            $this->updateModel($request, $userRequested, $attributes);
            unset($userRequested->roleAuth);
            $userRequested->save();
            return response()->json(['data'=>$userRequested], 200);
        }else{
            $errorJSON = ['error'   => 'Unauthorized'
                            , 'code' => 403];
            return response()->json($errorJSON, 403);
        }
    }

	  public function updatePassword(Request $request, $id)
    {
       // $password = \Auth::attempt(['email' => $request->email, 'password' =>$request->password]);
	   // $response = ['pass' => $password];
                    // return response()->json($response,422);
        $user = User::find($id);
		 if($user){
            $userRequested = \Auth::User();
            if($userRequested->id == $user->id){
                $messages = User::getMessages();
                $validation = User::getValidationsPassword();
                $v = Validator::make($request->all(),$validation,$messages);
                //SE VERIFICA SI ALGUN CAMPO NO ESTA CORRECTO
                if($v->fails()){
                    $response = ['error' => 'Bad Request', 'data' => $v->messages(),'code' => 422];
                    return response()->json($response,422);
                }
				$pass = \Auth::attempt(['email' => $request->email, 'password' =>$request->password]);
				if(!$pass){
					$response = ['error' => 'Bad Request', 'data' => 'La contraseña proporcionada no es correcta','code' => 422];
                    return response()->json($response,422);
				}
				if($request->passwordNew != $request->passwordConfirm){
                    $response = ['error' => 'Bad Request', 'data' => 'Las contraseñas no coinciden','code' => 422];
                    return response()->json($response,422);
                }

                $user->password = \Hash::make($request->passwordNew);
				// $user->update_id = $userRequested->id;//quien modifico

                $row = $user->save();

                if ($row != false) {
                    $response = ['code' => 200, 'message' => 'La contraseña fue modificada exitosamente!'];
                    return response()->json($response, 200);
                } else {
                    $response = ['error' => 'Un error ha ocurrio cuando se trato de actualizar la contraseña, contacte al equipo de soporte', 'code' => 500];
                    return response()->json($response, 500);
                }

            }else{
               $response = ['error' => 'Unauthorized','code' => '404'];
                return response()->json($response,403);
            }

        }else{
			$error = 'The user does not exist';
            $response = ['error' => $error,'code' => '404'];
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
        $userRequested = \Auth::User();
        //check if the user who requested the resource is the same
        //as the resource been requested
        if($userRequested->id == $id){
            $userRequested->delete();
            $respDelete = ['message'=> 'User deleted correctly'];
            return response()->json(['data'=>$respDelete], 200);
        }else if ($userRequested->roleAuth == 'ADMIN'){
            $user = User::find($id);
            if(!is_null($user)){

                $user->role_id = $userRequested->id;
                $user->role = $this->user_roles[$userRequested->roleAuth];
                $user->save();
                $row = $user->delete();

                if($row != false){
                    $response = ['code' => 200,'message' => "User was deleted succefully"];
                    return response()->json($response,200);
                }else{
                    $response = ['error' => 'It has occurred an error trying to delete the user','code' => 404];
                    return response()->json($response,500);
                }
            }else{
                //EN DADO CASO QUE EL ID DEL USER NO SE HALLA ENCONTRADO
                $response = ['error' => 'User does not exist','code' => '404'];
                return response()->json($response,404);
            }

        }else{
            $errorJSON = ['error'   => 'Unauthorized'
                            , 'code' => 403];
            return response()->json($errorJSON, 403);
        }
    }

	public function confirm(Request $request,$code){
        $user = User::where('token', '=', $code)->first();
        if($user){
            $user->confirmed = true;
            $user->token = null;
            $save = $user->save();
            if($save){
                $response = ['code' => 200
                                ,'message' => "Email confirmed correctly"
                                ,'data' => $user];
                            return response()->json($response,200);
            }else{
                $response = ['code' => 500
                        ,'error' => "Algun error ha ocurrido cuando se trato de confirmar tu correo, contacta al equipo de soporte"];
                        return response()->json($response,500);
            }
        }else{
            $response = ['code' => 403
                        ,'error' => "El usuario con el codigo de verificacion no fue encontrado"];
                        return response()->json($response,403);
        }
    }

	public function predict(Request $request){
		//SE VALIDA QUE SE HALLA ENVIADO LA FRASE
		if($request->phrase){

			//SE BUSCA EL TOKEN Y SE VALIDA QUE NO HALLA EXPIRADO, SI YA EXPIRO ENTONCES SE BUSCA OTRO NUEVO
			$time = time();
			$token = \DB::table('token_prediction')->where('expired','>',$time)->orderBy('id','desc')->take(1)->get();
			$phrase = $request->phrase;

			if($token != null){
				$token = $token[0]->token;
			}else{
				$token = $this::getPredictionToken();
			}

			$detectedCategory = $this::predictAPI($token,$phrase);

			$response = ['code' => 200,'data' => $detectedCategory];
			return response()->json($response,200);
		}else{
			$response = ['code' => 403,'error' => "Phrase not found"];
            return response()->json($response,403);
		}
	}

	/**
	* Este método solicita un token para poder usar la API Google Prediction
	*/
	private function getPredictionToken(){
		$header = '{"alg":"RS256","typ":"JWT"}';
		$headerBase64 = base64_encode($header);
		$headerBase64 = str_replace(array('+', '/', '\r', '\n', '='),array('-', '_'),$headerBase64);//A esto se llema safeBase64

		$issued = time();
		$expire = $issued + 3600; //los token maximo duran una hora
		
		$predict_service_acc = env('PREDICT_SERVICE_ACC');

		$payload = '{"iss":"'.$predict_service_acc .'","scope":"https://www.googleapis.com/auth/prediction","aud":"https://www.googleapis.com/oauth2/v4/token","exp":'.$expire.',"iat":'.$issued.'}';

		$payloadBase64 = base64_encode($payload);
		$payloadBase64 = str_replace(array('+', '/', '\r', '\n', '='),array('-', '_'),$payloadBase64);//A esto se llema safeBase64

		$secret = env('PREDICT_SECRET');
		$s = $headerBase64.".".$payloadBase64;

		$rsa = new \Crypt_RSA();
		$rsa->loadKey($secret);
		$rsa->setHash("sha256");
		$rsa->setSignatureMode(CRYPT_RSA_SIGNATURE_PKCS1);
		$signature = $rsa->sign($s);

		$signatureBase64 = base64_encode($signature);
		//$signatureBase64 = str_replace(array('+', '/', '\r', '\n', '='),array('-', '_'),$signatureBase64);

		$jwt = urlencode($s.".".$signatureBase64);

		//------------- ESTE CODIGO ES PARA OBTENER UN TOKEN Y PODER HACER CONSULTAS A LA API DE GOOGLE PREDICTION --------------
		$postvars = "grant_type=urn:ietf:params:oauth:grant-type:jwt-bearer&assertion=".$jwt;

		$temp = curl_init("https://www.googleapis.com/oauth2/v4/token");
		curl_setopt($temp, CURLOPT_POST, true);
		curl_setopt($temp, CURLOPT_POSTFIELDS, $postvars);
		curl_setopt($temp, CURLOPT_SSL_VERIFYPEER , false );
		curl_setopt($temp, CURLOPT_HTTPHEADER, array('Content-Type: application/x-www-form-urlencoded'));
		curl_setopt($temp, CURLOPT_RETURNTRANSFER, true);
		if (!$data = curl_exec($temp)) {
			return "UNKNOWN ERROR";
		} else {
			curl_close($temp);

			if(strpos($data,'error')){
				$result = json_decode($data, true);
				$error = $result['error'];
				$error_desc = $result['error_description'];
				return "Error: ".$error." Error description: ".$error_desc;
			}else{
				$result = json_decode($data, true);
				$token = $result['access_token'];

				\DB::table('token_prediction')->truncate();

				//SE GUARDA EL TOKEN
				\DB::table('token_prediction')->insert([
					'token' => $token,
					'expired' => $expire,
					'issued' => $issued,
					'created_at' => date('Y-m-d h:i:s',time()),
					'updated_at' => date('Y-m-d h:i:s',time())
				]);

				return $token;
			}
		}
	}

	/**
	* Método para detectar a que categoría pertenece una frase.
	* @param string $token token requerido para poder hacer uso de Google Prediction
	* @param string $phrase frase a buscar
	* @return string cada de texto con la mejor categoria elegida por Google Prediction
	*/
	private function predictAPI($token,$phrase){
		//--------------ESTE CODIGO ES PARA MANDAR UNA FRASE Y QUE GOOGLE PREDICTION NOS REGRESE A QUE CATEGORIA PERTENECE---------------
		$data = '{"input":{"csvInstance":["'.$phrase.'"]}}';
		$data_string = $data;
		$model_url = env('PREDICT_MODEL');
		$temp = curl_init($model_url);
		curl_setopt($temp, CURLOPT_CUSTOMREQUEST, "POST");
		curl_setopt($temp, CURLOPT_POSTFIELDS, $data_string);
		curl_setopt($temp, CURLOPT_SSL_VERIFYPEER , false );
		curl_setopt($temp, CURLOPT_HTTPHEADER, array('Content-Type: application/json','Authorization: Bearer '.$token.' '));
		curl_setopt($temp, CURLOPT_RETURNTRANSFER, true);
		if (!$data = curl_exec($temp)) {
			return "UNKNOW ERROR";
		} else {
			curl_close($temp);

			//SE VALIDA QUE LA RESPUESTA NO CONTENGA ERRORES, SI CONTIENE ENTONCES SE MUESTRAN LOS ERRORES
			if(strpos($data,'error')){

				$result = json_decode($data, true);
				$code = $result['error']['code'];
				$message = $result['error']['message'];
				$errors = $result['error']['errors'];
				$location = $errors[0]['location']; //En la primera posicion estan los errores
				return "Codigo: ".$code." Message: ".$message." Location: ".$location;

			}else{

				$result = json_decode($data, true);
				return $result['outputLabel'];

				//ESTAS SON LAS DEMAS CATEGORIAS QUE PUEDEN SER POSIBLES RESULTADOS DE LA FRASE BUSCADA
				/*for($i = 0;$i < count($result['outputMulti']);$i++){
					$output = $result['outputMulti'][$i];
					$categoria = $output['label'];
					$puntuacion = $output['score'];
					echo $categoria."  ".$puntuacion."</br>";
				}*/

			}
		}
	}

	public function storeSearched(Request $request){
		$id = \DB::table('search_log')->insertGetId([
			"ip" => $request->ip,
			"search_term" => $request->search_term,
			"detected_category" => $request->detected_category,
			'created_at' => date('Y-m-d h:i:s',time()),
			'updated_at' => date('Y-m-d h:i:s',time())
		]);

		$log = new \stdClass;
		$log->id = $id;

		$response = ['code' => 200,'data' => $log];
		return response()->json($response,200);
	}

	public function updateSearched(Request $request, $id){

		$inputs = $request->all();

		if($request->correct_date != null)
			$inputs['correct_date'] = date('Y-m-d h:i:s',time());

		\DB::table('search_log')->where('id',$id)->update($inputs);

		$response = ['code' => 200];
		return response()->json($response,200);
	}
}

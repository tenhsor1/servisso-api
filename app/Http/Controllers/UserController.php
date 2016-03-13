<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\Mailers\AppMailer;
use App\Http\Controllers\Controller;
use App\User;
use JWTAuth;

class UserController extends Controller
{
    public function __construct(){
        $this->middleware('jwt.auth:user|admin', ['except' => ['store','predict']]);
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
        return "index";
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\UserStoreRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Requests\UserStoreRequest $request)
    {
        $newUser = User::create($request->all());

        $extraClaims = ['role'=>'USER'];
        $token = JWTAuth::fromUser($newUser,$extraClaims);
        $reflector = new \ReflectionClass('JWTAuth');
        $newUser->access = $token;
        if($newUser){
            $this->mailer->sendVerificationEmail($newUser);
            $response = ['data' => $newUser
                        ,'code' => 200
                        ,'message' => 'User was created succefully'];
            return response()->json($response,200);
        }
        $response = ['error' => 'It has occurred an error trying to save the user'
                    ,'code' => 404];
        return response()->json($response,404);
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
                            , 'password'
                            , 'last_name'
                            , 'phone'
                            , 'address'
                            , 'zipcode'
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
        }else{
            $errorJSON = ['error'   => 'Unauthorized'
                            , 'code' => 403];
            return response()->json($errorJSON, 403);
        }
    }

	public function confirm(Request $request){
        $validation = ['code' => 'string|required|min:30'];
        $messages = User::getMessages();
        $v = Validator::make($request->all(),$validation,$messages);
        if($v->fails()){
            $response = ['error' => 'Bad Request', 'data' => $v->messages(),'code' => 422];
            return response()->json($response,422);
        }
        $user = User::where('token', '=', $request->code)->first();
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
                        ,'error' => "Something happened when trying to confirm the email"];
                        return response()->json($response,500);
            }
        }else{
            $response = ['code' => 403
                        ,'error' => "User with code validation not found"];
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

		$payload = '{"iss":"servsl@testwebdev-962.iam.gserviceaccount.com","scope":"https://www.googleapis.com/auth/prediction","aud":"https://www.googleapis.com/oauth2/v4/token","exp":'.$expire.',"iat":'.$issued.'}';

		$payloadBase64 = base64_encode($payload);
		$payloadBase64 = str_replace(array('+', '/', '\r', '\n', '='),array('-', '_'),$payloadBase64);//A esto se llema safeBase64

		$secret = "MIIEvAIBADANBgkqhkiG9w0BAQEFAASCBKYwggSiAgEAAoIBAQCHHOn151C+YgQhn2w0/PJhgmq/O0WRHxZMZcN0K4WaryQsUvTbFI8+BJe/jioRIO7lypYD4P3lxG2QGvMK5PF/pJrgTR+tE9Mj8t+NKU31oMw6Q0fqNzb5TnHbhkd6ZN3xtT6Khguwkb82cZRVus1RxGRpq5PL/xDxpiXGAUjb56XQXe6kwMVI+Ag9C7XRxrntptCTpTPSUlccWR7DafiXh+ETABeuKGpW7PpUdnUOrut11Ydj7UgD8eDyhznd3FKCxoTaPih0VEiJjyxZXvvChRdr7NOpN15MLDJZ1aI6vkF4xNYiR7qPZOQYwZOYVUNduQFXarerHR/jH6fINPTRAgMBAAECggEAI6/RY+/q9b4x1SekjwJYisTFqSjgoQoS+67NRzvPmCG2bjajEdKGWx0fb6r/FXMbZnpx0Sh2J2AQiEV1+GSsHMi/V4tHWJGp7Q7TWReVzdDg4Gqw7f4TeRntHMyEyKEnthXnJPNu1v5IAPtS8KncXUKAOyDkcrc2JH178KaaNeq8tZMwxrW4OHL36K6zBviciPXrkkUZtoS7jKbDpnojHJ71s4yNGIMb+61W2GGJGNtVfbbuhmZiShrxUSSX4Mr0PlY7uJftV/GPnUgS1DQ40gr5R/xX+H4WakMZk7jikGUm0Ws7vW4RjliyhIxryH29sOhMGKn9iVkOkcxia2HOGQKBgQD7YgRjbDf+YqfpiPFr1Xftcqc+4VRmhSxX6AHNw+QjpMBXQeaFFoSzlRhWlvMr2TZIuQuh3ByZDGP/zm9+IJz7bwUljNwM18sA5sXLma31XAXfz+ISqL5Uraq5Bw6WOUWLWzS38DY2TAf4pTm2KvXWnTyZX0jsumM3/kT+mXTNGwKBgQCJmDRb1oEpXP42Xw5fjZJzmqPtgTSsW1bjhz6ODxWHeL0Ihco0Xqswm6ez1cLqpC0lk4VNEHuivnMtxuwmQnw5+42jlheDKrw4XGytjzoLx5sAUEScE2bsGEllWInYX2qVqOA6dm05bIg9N4CkmAgbZuxQxAOH/CJ3XZQdC1AAgwKBgF/qoF4HNr47inITLHrGssHJE4NsmrWbbrYD8lw+uFfZTwJ8RKbXVr7mzqiLZDGA6bOJ16Rkxgynq6g5blUjwII3dDFFs9i6pdysMSBkfPm3qQ4i1dHkzOqmcRO0W556L8ziehUM9MJ29DutX33gmnjO+gZTUxHwdFczD8RNbUGtAoGAb8795Q7mwDzv2jDeFimNs2EbCllu+wvyDEwPOhLp1L75JR7K1EmFZKdn3Eu86zzj7t/0d04ImZOXNsCpjuGB3wAZ9a92hcDJWCdKrLJxYbcerl+LkSR3Ay0tHyyWPvwyOVEUfI1Vbk9SWiRq5dUg6Vt2dp8Bm5P4UfT58awKo48CgYBi9UL5bNIQYwjOR0XBzOZ6NvNGAFEuTKr1gQf8gpZN+8yf/OSl8TGKc7jQeb5Oh8U0Qir7kzV/GMgOFsUdt7pND6yFrUkFeQ0iItFkcccWVVapcW5IP967GlWep5Cq88IgobXyic4eg7aq0lu64ltBJYLrQi+wjpONgWuGe1AfeA";

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
		$postvars = "grant_type=urn%3Aietf%3Aparams%3Aoauth%3Agrant-type%3Ajwt-bearer&assertion=".$jwt;

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
					'issued' => $issued
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

		$temp = curl_init("https://www.googleapis.com//prediction/v1.6/projects/870494030602/trainedmodels/TrainingModelServ/predict");
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
}

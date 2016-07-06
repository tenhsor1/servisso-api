<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Service;
use App\Branch;
use App\ServiceImage;
use App\Guest;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use App\Extensions\Utils;
use Validator;
use App\Mailers\AppMailer;


class ServiceController extends Controller
{
    public function __construct(){
        $this->middleware('jwt.auth:admin', ['only' => ['destroy']]);
        $this->middleware('jwt.auth:user', ['only' => ['update', 'showFromBranch', 'indexPerCompany','taskUser','task']]);
        $this->middleware('jwt.auth:user|admin', ['only' => ['index']]);
        $this->middleware('default.headers');
        $this->userTypes = \Config::get('app.user_types');
		$this->mailer = new AppMailer();
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $user = \Auth::User();
        $services = [];
        if($user->roleAuth == 'ADMIN'){
            $services = Service::with('branch')
                                ->with('userable')
                                ->with('userRate')
                                ->with('partnerRate')
                                ->searchBy($request)
                                ->betweenBy($request)
                                ->orderByCustom($request)
                                ->limit($request)
                                ->get();
        }else if($user->roleAuth == 'USER'){
            $services = Service::whereUser($user->id)
                                ->with('branch')
                                ->with('userable')
                                ->with('userRate')
                                ->with('partnerRate')
                                ->searchBy($request)
                                ->betweenBy($request)
                                ->orderByCustom($request)
                                ->limit($request)
                                ->get();
        }
        return response()->json(['data'=>$services], 200);
    }

    public function indexPerCompany(Request $request, $companyId)
    {

        $services = Service::whereCompany($companyId)
                                ->with('userable')
                                ->with('userRate')
                                ->with('partnerRate')
                                ->searchBy($request)
                                ->betweenBy($request)
                                ->orderByCustom($request)
                                ->limit($request)
                                ->get();

        $count = $services->count();
        $response = ['count' => $count,'code' => 200,'data' => $services];
        return response()->json($response,200);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $user = $this->checkAuthUser('user');
        if($user && !is_array($user)){

        }else{
            $guestId = $request->input('guest_id');
            $user = Guest::find($guestId);
        }
        if($user){

			$rules = Service::getRules();
			$messages = Service::getMessages();

			$validator = Validator::make($request->all(),$rules,$messages);

			if($validator->fails()){
				$response = ['error' => $validator->errors(),'message' => 'Bad request','code' => 400];
				return response()->json($response,400);
			}

            $service = new Service;

            $service->description = $request->input('description');
            $service->branch_id = $request->input('branch_id');
            $service->address = $request->input('address');
            $service->phone = $request->input('phone');
            $service->zipcode = $request->input('zipcode');

            $save = $user->services()->save($service);

			$branch = Branch::find($service->branch_id);

            if($save){

				$baseUrl = \Config::get('app.front_url');

				//Si es una branch no registrada(inegi) y tiene email, se envia un email para
				//que el asociado se registre
				if($branch->inegi && $branch->email){

					$company_code = \Crypt::encrypt($branch->company_id);

					$data = [
						'btn_url' => $baseUrl.'/auth/sucursal/'.$company_code,
						'client_name' => $user->name,
						'created_date' => $service->created_at->format('M d, Y g:i a'),
						'problem_description' => $service->description,
						'branch_email' => $branch->email,
						'branch_name' => $branch->name
					];
					$this->mailer->sendNonRegisteredBranchEmail($data);

				//Si es una branch registrada
				}else{

					$data = [
						'service_url' => $baseUrl.'/panel/servicios/'.$branch->id.'/'.$service->id,
						'client_name' => $user->name,
						'created_date' => $service->created_at->format('M d, Y g:i a'),
						'problem_description' => $service->description,
						'user_email' => $branch->company->user->email,
						'branch_name' => $branch->name
					];
					$this->mailer->sendRegisteredBranchEmail($data);
				}

                $tokenImage = \Crypt::encrypt(['service_id' => $service->id
                                                ,'date' => $service->created_at->format('Y-m-d H:i:s')]);

                $service->token_image = $tokenImage;
                return response()->json(['data'=>$service], 200);

            }else{
                return response()->json([
                    'error' => 'It has occurred an error trying to save the server'
                    ,'code' => 500], 500);
            }

        }else{
            $errorJSON = ['error'   => 'Bad request'
                            , 'code' => 422
                            , 'data' => [
                                'guest_id'=> ['Missing the id of the user/guest requesting the service']
                                ]];
            return response()->json($errorJSON, 422);
        }
    }

    public function setImages(Request $request, $serviceId){

        $resp = $this->validateImageToken($serviceId, $request->header('X-Service-Token'));
        if($resp['code'] > 200){
            return response()->json($resp, $resp['code']);
        }
        $service = Service::find($serviceId);
        if(!$service){
            $response = ['ext' => $ext, 'error' => "No existe el servicio", 'code' =>  404];
            return response()->json($response,404);
        }

        $ext = $request->file('image')->getClientOriginalExtension();
        // Se verifica si es un formato de imagen permitido
        if($ext !='jpg' && $ext !='jpeg' && $ext !='bmp' && $ext !='png'){
            $response = ['ext' => $ext, 'error' => "Sólo imágenes de extensión jpg, jpeg, bmp and png", 'code' =>  422];
            return response()->json($response,422);
        }
        $img = Utils::StorageImage($serviceId,$request->file('image'), 'services/images/', 'services/thumbs/');
        $serviceImage = new ServiceImage();
        $serviceImage->image = $img['image'];
        $serviceImage->thumbnail = $img['thumbnail'];

        $saveImage = $service->images()->save($serviceImage);

        if($saveImage != false){
            $response = ['code' => 200, 'data' => $saveImage, 'message' => 'Image was save succefully'];
            return response()->json($response,200);
        }else{
            $response = ['error' => 'It has occurred an error trying to update the service image','code' => 500];
            return response()->json($response,500);
        }

    }

    private function validateImageToken($serviceId, $headerToken){
        try{
            $imageToken = \Crypt::decrypt($headerToken);
        }catch(DecryptException $e){
            $data = ['token' => ["El token no es válido"]];
            $response = ['data' => $data, 'error' => 'Bad request', 'code' => 403];
            return $response;
        }

        $tokenServiceId = $imageToken['service_id'];
        $tokenDate = $imageToken['date'];
        if($tokenServiceId != $serviceId){
            $data = ['token' => ["El token no es válido"]];
            $response = ['data' => $data, 'error' => 'Bad request', 'code' => 403];
            return $response;
        }
        if(time() > strtotime($tokenDate. ' + 1 hours')){
            $data = ['token' => ["El token ha expirado"]];
            $response = ['data' => $data, 'error' => 'Bad request', 'code' => 403];
            return $response;
        }
        return ['code' => 200];
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $user = $this->checkAuthUser();

        if(is_array($user)){
            return response()->json($user, $user['code']);
        }elseif (!$user) {
            return response()->json(['error'=> 'Unauthorized', 'code'=>403], 403);
        }

        $userId = 0;
        if($user){
            $userId = $user->id;
        }
        $conditions = [ 'id' => $id
                        , 'userable_id' => $userId
                        , 'userable_type' => $this->userTypes['user']];
        $service = Service::where($conditions)->with('images')->first();
        if($service){
            return response()->json(['data'=>$service], 200);

        }else{
            $errorJSON = ['error'   => 'The resource doesn\'t exist'
                            , 'code' => 404
                            , 'data' => [
                                'user_id'=> ['The user doesn\'t have this service']
                                ]];
            return response()->json($errorJSON, 404);
        }
    }

    public function showFromBranch($id)
    {
        $userRequested = \Auth::User();

        $service = Service::whereUser($userRequested->id)
                            ->where(['services.id' => $id])
                            ->with('images')
                            ->with('userable')
                            ->with('branch')
                            ->first();
        if($service){
            return response()->json(['data'=>$service], 200);

        }else{
            $errorJSON = ['error'   => 'The resource doesn\'t exist'
                            , 'code' => 404
                            , 'data' => [
                                'user_id'=> ['The user doesn\'t have this service']
                                ]];
            return response()->json($errorJSON, 404);
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
        $user = \Auth::User();
        $userId = 0;
        if($user){
            $userId = $user->id;
        }
        $conditions = [ 'id' => $id
                        , 'userable_id' => $userId
                        , 'userable_type' => $this->userTypes['user']];
        $service = Service::where($conditions)->first();
        if($service){
            $service->description = $request->input('description');
            $service->save();
            return response()->json(['data'=>$service], 200);

        }else{
            $errorJSON = ['error'   => 'The resource doesn\'t exist'
                            , 'code' => 422
                            , 'data' => [
                                'user_id'=> ['The user doesn\'t have this service']
                                ]];
            return response()->json($errorJSON, 422);
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


	public function taskUser($id)
    {
		$userRequested = \Auth::User();

        $service = Service::where(['services.userable_id' => $id])
                            ->with('branch')
                            ->with('branch.company')
                            ->get();
        if($service){
            return response()->json(['data'=>$service], 200);

        }else{
            $errorJSON = ['error'   => 'The resource doesn\'t exist'
                            , 'code' => 404
                            , 'data' => [
                                'user_id'=> ['The user doesn\'t have this service']
                                ]];
            return response()->json($errorJSON, 404);
        }
    }

	public function task($id)
    {
		$userRequested = \Auth::User();

        $service = Service::where(['services.id' => $id])
                            ->with('branch')
                            ->with('branch.company')
							->with('images')
                            ->first();
        if($service){
            return response()->json(['data'=>$service], 200);

        }else{
            $errorJSON = ['error'   => 'The resource doesn\'t exist'
                            , 'code' => 404
                            , 'data' => [
                                'user_id'=> ['The user doesn\'t have this service']
                                ]];
            return response()->json($errorJSON, 404);
        }
    }

}
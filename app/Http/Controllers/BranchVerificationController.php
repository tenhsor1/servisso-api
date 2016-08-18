<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\BranchVerification;
use App\Branch;
use Validator;
use JWTAuth;

class BranchVerificationController extends Controller
{
	
	public function __construct(){
        $this->middleware('jwt.auth:user|admin', ['only' => ['store','update','destroy']]);
        $this->middleware('default.headers');
		$this->user_roles = \Config::get('app.user_roles');
    }
	
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {

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
    public function store(Request $request,$id)
    {
        $messages = BranchVerification::getMessages();
		$rules = BranchVerification::getRules();

		$v = Validator::make($request->all(),$rules,$messages);

		//SE VERIFICA SI ALGUN CAMPO NO ESTA CORRECTO
		if($v->fails()){
			$response = ['error' => $v->errors(), 'message' => 'Bad request', 'code' =>  400];
			return response()->json($response,400);
		}
		
		//SE OBTIENE EL ID DE LA BRANCH
		$branch_id = $id;
		$branch = Branch::find($branch_id);

		if($branch){
			
			$userRequested = \Auth::User();
			$role = $userRequested->authRole;
			
			//SE VERIFICA QUE EL USER QUE HIZO LA PETICION LE PERTENEZCA LA BRANCH
			if(($userRequested->id == $branch->company->user_id) || $role == 'ADMIN'){
				
				//SE UNA INSTANCIA DE BRANCH VERIFICATION
				$branch_verification = new BranchVerification;
				$branch_verification->branch_id = $branch_id;
				$branch_verification->verification_type = $request->verification_type;
				$branch_verification->description = $request->description;
				$branch_verification->url_verification_img = $request->url_verification_img;
				$branch_verification->save();
				
				//SE VALIDA QUE SE HALLA GUARDADO
				if($branch_verification){

					$response = ['data' => $branch_verification,'code' => 200,'message' => 'Branch verification was created succefully'];
					return response()->json($response,200);
				}else{
					$response = ['error' => 'It has occurred an error trying to save the branch verification','code' => 500];
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
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
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
    public function update(Request $request, $id_branch, $id_verification)
    {	

		$messages = BranchVerification::getMessages();
		$rules = BranchVerification::getRules();

		$v = Validator::make($request->all(),$rules,$messages);

		//SE VERIFICA SI ALGUN CAMPO NO ESTA CORRECTO
		if($v->fails()){
			$response = ['error' => $v->errors(), 'message' => 'Bad request', 'code' =>  400];
			return response()->json($response,400);
		}
		
		//SE OBTIENE EL ID DE LA BRANCH
		$branch_id = $id_branch;
		$branch = Branch::find($branch_id);

		if($branch){
			
			$userRequested = \Auth::User();
			$role = $userRequested->authRole;
			
			//SE VERIFICA QUE EL USER QUE HIZO LA PETICION LE PERTENEZCA LA BRANCH
			if(($userRequested->id == $branch->company->user_id) || $role == 'ADMIN'){
				
				//SE UNA INSTANCIA DE BRANCH VERIFICATION
				$branch_verification = BranchVerification::find($id_verification);
				$branch_verification->branch_id = $branch_id;
				$branch_verification->verification_type = $request->verification_type;
				$branch_verification->description = $request->description;
				$branch_verification->url_verification_img = $request->url_verification_img;
				
				$branch->verifications()->save($branch_verification);
				
				//SE VALIDA QUE SE HALLA GUARDADO
				if($branch_verification){

					$response = ['data' => $branch_verification,'code' => 200,'message' => 'Branch verification was created succefully'];
					return response()->json($response,200);
				}else{
					$response = ['error' => 'It has occurred an error trying to save the branch verification','code' => 500];
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
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id_branch,$id_verification)
    {
		//SE OBTIENE EL ID DE LA VERIFICATION
					
		$branch = Branch::find($id_branch);

		if($branch){
			$userRequested = \Auth::User();
			$role = $userRequested->authRole;
			
			//SE VERIFICA QUE EL USER QUE HIZO LA PETICION LE PERTENEZCA LA BRANCH
			if(($userRequested->id == $branch->company->user_id) || $role == 'ADMIN'){
				
				$branch_verification = BranchVerification::find($id_verification);		
				$rows = $branch_verification->delete();
				
				if($rows > 0){
					$response = ['code' => 200,'message' => "Branch verification was deleted succefully"];
					return response()->json($response,200);
				}else{
					$response = ['error' => 'It has occurred an error trying to delete the branch verification','code' => 500];
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
    }
}

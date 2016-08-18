<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Admin;
use App\ContactUs;
use App\Service;
use App\Task;
use Validator;
use JWTAuth;

class AdminController extends Controller
{
    public function __construct(){
        $this->middleware('jwt.auth:admin', ['only' => ['update','show','store','index','destroy','requirements']]);
        $this->middleware('default.headers');
        $this->AdminRole = \Config::get('app.admin_roles');
        //$this->UserRoles = \Config::get('app.user_roles');
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $adminRequested = \Auth::User();
        if($adminRequested->role_id == $this->AdminRole['SUPER']){
            $admin = Admin::searchBy($request)
                                ->betweenBy($request)
                                ->orderByCustom($request)
                                ->limit($request)
                                ->get();
			$count = $admin->count();
			 if(!is_null($admin)){
                $response = ['code' => 200,'Count' => $count,'data' => $admin];
                return response()->json($response,200);
            }else{
                $response = ['error' => 'Admin are empty','code' => 404];
                return response()->json($response,404);
            }
        }else{
            $errorJSON = ['error'   => 'Unauthorized', 'code' => 403];
            return response()->json($errorJSON, 403);
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
        if($adminRequested->role_id == $this->AdminRole['SUPER']) {

            $messages = Admin::getMessages();
            $validation = Admin::getValidations();
            $v = Validator::make($request->all(), $validation, $messages);

            //SE VERIFICA SI ALGUN CAMPO NO ESTA CORRECTO
            if ($v->fails()) {
                $response = ['error' => 'Bad Request', 'data' => $v->messages(), 'code' => 422];
                return response()->json($response, 422);
            }

            //SE CREA UN ADMIN
            $admin = new Admin;
            $admin->email = $request->email;
            $admin->password = $request->password;
            $admin->name = $request->name;
            $admin->last_name = $request->last_name;
            $admin->address = $request->address;
            $admin->phone = $request->phone;
            $admin->zipcode = $request->zipcode;
            $admin->state_id = $request->state_id;
            $admin->country_id =$request->country_id;
            $admin->role_id = $request->role_id;
            $admin->update_id = $adminRequested->id;//admin que modifico


            //$admin = Admin::create($request->all());
            $admin->save();

            if (!is_null($admin)) {
                $response = ['code' => 200, 'message' => 'Admin was created succefully'];
                return response()->json($response, 200);
            } else {
                $response = ['error' => 'It has occurred an error trying to save the admin', 'code' => 500];
                return response()->json($response, 500);
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
        $adminRequested = \Auth::User();
        if($adminRequested->id == $id || $adminRequested->role_id == $this->AdminRole['SUPER']){
            $admin = Admin::find($id);
            if(!is_null($admin)){
                $response = ['code' => 200,'data' => $admin];
                return response()->json($response,200);
            }else{
                $response = ['error' => 'Admin does no exist','code' => 404];
                return response()->json($response,404);
            }
        }else{
            $errorJSON = ['error'   => 'Unauthorized'
                , 'code' => 403];
            return response()->json($errorJSON, 403);
        }
    }


    /**
     * Update the specified resource in storage.
     *
     * @param Requests\AdminUpdateRequest|Request $request
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {

        $admin = Admin::find($id);

        if(!is_null($admin)){

            $adminRequested = \Auth::User();//quien hizo la peticion
            if($adminRequested->id == $admin->id || $adminRequested->role_id == $this->AdminRole['SUPER']){//se valida quien mando la peticion le pertenecen sus datos

                $messages = Admin::getMessages();
                $validation = Admin::getValidations();
                $v = Validator::make($request->all(),$validation,$messages);
                //SE VERIFICA SI ALGUN CAMPO NO ESTA CORRECTO
                if($v->fails()){
                    $response = ['error' => 'Bad Request', 'data' => $v->messages(),'code' => 422];
                    return response()->json($response,422);
                }



                $admin->email = $request->email;
                $admin->password = $request->password;
                $admin->name = $request->name;
                $admin->last_name = $request->last_name;
                $admin->address = $request->address;
                $admin->phone = $request->phone;
                $admin->zipcode = $request->zipcode;
                $admin->state_id = $request->state_id;
                $admin->country_id =$request->country_id;
                $admin->role_id = $request->role_id;
                $admin->update_id = $adminRequested->id;//quien modifico


                //$admin = Admin::create($request->all());
                $row = $admin->save();

                if ($row != false) {
                    $response = ['code' => 200, 'message' => 'Admin was modify succefully'];
                    return response()->json($response, 200);
                } else {
                    $response = ['error' => 'It has occurred an error trying to upate the admin', 'code' => 500];
                    return response()->json($response, 500);
                }

            }else{
                //EN DADO CASO QUE EL ID DEL ADMIN NO LE PERTENEZCA
                $response = ['error' => 'Unauthorized','code' => '404'];
                return response()->json($response,404);
            }

        }else{
            //EN DADO CASO QUE EL ID DEL ADMIN NO SE HALLA ENCONTRADO
            $response = ['error' => 'Admin does not exist','code' => '404'];
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
        $adminRequested = \Auth::User();
        if($adminRequested->role_id == $this->AdminRole['SUPER']) {
            $admin = Admin::find($id);
            if(!is_null($admin)){
				$admin->update_id = $adminRequested->id;//quien modifico
				$admin->save();
                //SE BORRAR EL admin
                $admin->delete();

                if(!is_null($admin)){
                    $response = ['code' => 200,'message' => "Admin was deleted succefully"];
                    return response()->json($response,200);
                }else{
                    $response = ['error' => 'It has occurred an error trying to delete the admin','code' => 404];
                    return response()->json($response,404);
                }



            }else{
                //EN DADO CASO QUE EL ID DEL ADMIN NO SE HALLA ENCONTRADO
                $response = ['error' => 'Admin does not exist','code' => '404'];
                return response()->json($response,404);
            }
        }else{
            $errorJSON = ['error'   => 'Unauthorized'
                , 'code' => 403];
            return response()->json($errorJSON, 403);
        }
    }
	
	/**
     * Get all the task and contact comment and questions.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
	 public function requirements(Request $request)
    {
		$userRequested = \Auth::User();
		$contact = ContactUs::select(\DB::raw("created_at,comment,'contact' as type,email,'Feedback/FQA' as sent_to"))
		;
		
		$service = Service::leftjoin('users as u', 'u.id', '=', 'services.userable_id')
		->leftjoin('branches', 'branches.id', '=', 'services.branch_id')
		->leftjoin('companies', 'companies.id', '=', 'branches.company_id')
		->leftjoin('users as ub', 'ub.id', '=', 'companies.user_id')
		->select(\DB::raw("services.created_at,services.description as comment,'service' as type, u.email,ub.email as sent_to"))
		;
		
		$task = Task::leftjoin('categories', 'categories.id', '=', 'tasks.category_id')
		->leftjoin('users', 'users.id', '=', 'tasks.user_id')
		->leftJoin(\DB::raw('(SELECT task_id, COUNT(*) total FROM task_branches GROUP BY task_id) tb'), function($join)
			{
				$join->on('tb.task_id', '=', 'tasks.id');
			})
		->select(\DB::raw("tasks.created_at,CONCAT(tasks.description ,', ',categories.name) as comment,'task' as type,users.email,CONCAT('',tb.total) as sent_to"))
		->groupBy('tasks.id')
		->groupBy('users.email')
		->groupBy('tb.total')
		->groupBy('categories.name');	
		
		$results = $contact->union($service)->union($task)
		->orderBy('created_at', 'desc')
		->get();
							
		$count = $results->count();
		if($results){
            $response = ['code' => 200,'count' => $count,'results' => $results];
            return response()->json($response,200);
        }else{
            $response = ['error' => 'The questions are empty','code' => 404];
            return response()->json($response,404);
		}
        
    }

}

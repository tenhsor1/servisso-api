<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use Validator;
use App\ContactUs;
use App\Service;
use App\Task;
use JWTAuth;
class ContactUsController extends Controller
{
	 public function __construct(){
        $this->middleware('jwt.auth:admin', ['only' => ['update','destroy','requirements']]);
		$this->middleware('jwt.auth:user', ['only' => ['update','index','show']]);
	}
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $userRequested = \Auth::User();
		  $contact = ContactUs::searchBy($request)
						->betweenBy($request)
						->orderByCustom($request)
						->limit($request)
						->get();
		$count = $contact->count();
		if(!is_null($contact)){
            $response = ['code' => 200,'Count' => $count,'data' => $contact];
            return response()->json($response,200);
        }else{
            $response = ['error' => 'The questions are empty','code' => 404];
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

	
	  public function store(Request $request)
    {
		$messages = ContactUs::getMessages();
        $validation = ContactUs::getValidations();
		$v = Validator::make($request->all(),$validation,$messages);
        //SE VERIFICA SI ALGUN CAMPO NO ESTA CORRECTO
        if($v->fails()){
            $response = ['error' => 'Bad Request', 'data' => $v->messages(), 'code' =>  422];
			return response()->json($response,422);
        }

			$contact = new ContactUs;
            $contact->email = $request->email;
            $contact->name = $request->name;
            $contact->comment = $request->comment;
			$row= $contact->save();

        if($row != false){
            $response = ['code' => 200,'message' => 'Comment was created succefully'];
            return response()->json($response,200);
        }else{
            $response = ['error' => 'It has occurred an error trying to save the comment','code' => 404];
            return response()->json($response,404);
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
        $contact = ContactUs::find($id);
        if(!is_null($contact)){
            $response = ['code' => 200,'data' => $contact];
            return response()->json($response,200);
        }else{
            $response = ['error' => 'Comment does no exist','code' => 404];
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
		$contact = ContactUs::find($id);
        if(!is_null($contact)){
			$userRequested = \Auth::User();
			if($userRequested->id == $contact->user_id || $userRequested->roleAuth  == "ADMIN"){
				$messages = ContactUs::getMessages();
                $validation = ContactUs::getValidations();
                $v = Validator::make($request->all(),$validation,$messages);
                //SE VERIFICA SI ALGUN CAMPO NO ESTA CORRECTO
                if($v->fails()){
                    $response = ['error' => 'Bad Request', 'data' => $v->messages(),'code' => 422];
                    return response()->json($response,422);
                }

                $contact = new ContactUs;
				$contact->email = $request->email;
				$contact->name = $request->name;
				$contact->comment = $request->comment;
				$contact->update_id = $userRequested->id;//id de quien modifico
                $row = $contact->save();
                if($row != false){
                    $response = ['code' => 200,'message' => 'Comment was update succefully'];
					return response()->json($response,200);
                }else{
                    $response = ['error' => 'It has occurred an error trying to update the comment','code' => 404];
                    return response()->json($response,404);
                }
			}else{
				$errorJSON = ['error'   => 'Unauthorized'
					, 'code' => 403];
				return response()->json($errorJSON, 403);
			}
		}else{
            //EN DADO CASO QUE EL ID DEL ADMIN NO SE HALLA ENCONTRADO
            $response = ['error' => 'Comment does not exist','code' => '404'];
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
		$contact = ContactUs::find($id);
        if(!is_null($contact)){
			$userRequested = \Auth::User();
			if($userRequested->id == $contact->user_id || $userRequested->roleAuth  == "ADMIN"){
					$contact->update_id = $userRequested->id;//id de quien modifico
					$contact->save();
					$rows = $contact->delete();
					if($rows > 0){
						$response = ['code' => 200,'message' => "Comment was deleted succefully"];
						return response()->json($response,200);
					}else{
						$response = ['error' => 'It has occurred an error trying to delete the Comment','code' => 404];
						return response()->json($response,404);
					}

			}else{
				$errorJSON = ['error'   => 'Unauthorized'
					, 'code' => 403];
				return response()->json($errorJSON, 403);
			}
		}else{
            //EN DADO CASO QUE EL ID DEL ADMIN NO SE HALLA ENCONTRADO
            $response = ['error' => 'Comment does not exist','code' => '404'];
            return response()->json($response,404);
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
		$contact = ContactUs::select(\DB::raw("created_at,comment,'contact' as type,email,'Feedback/FQA' as sent_to"));
		
		$service = Service::leftjoin('users as u', 'u.id', '=', 'services.userable_id')
		->leftjoin('branches', 'branches.id', '=', 'services.branch_id')
		->leftjoin('companies', 'companies.id', '=', 'branches.company_id')
		->leftjoin('users as ub', 'ub.id', '=', 'companies.user_id')
		->select(\DB::raw("services.created_at,services.description as comment,'service' as type, u.email,ub.email as sent_to"));	
		
		$task = Task::leftjoin('categories', 'categories.id', '=', 'tasks.category_id')
		->leftjoin('users', 'users.id', '=', 'tasks.user_id')
		
		->select(\DB::raw("tasks.created_at,CONCAT(tasks.description ,', ',categories.name) as comment,'task' as type,users.email,'0' as sent_to"));	
		
		$results = $contact->union($service)->union($task)
							->searchBy($request)
							->betweenBy($request)
							->orderByCustom($request)
							->limit($request)
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

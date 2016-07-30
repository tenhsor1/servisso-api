<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\UserRate;
use JWTAuth;
use Validator;
use App\Service;
use App\Branch;
use App\TaskBranch;
use App\User;
use App\Company;

class UserRateController extends Controller
{

	public function __construct(){
        $this->middleware('jwt.auth:user', ['only' => ['store','show','update','destroy']]);
        $this->middleware('default.headers');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $rates = UserRate::with('tasks')->get();
		$response = ['data' => $rates,'code' => 200];
		return response()->json($rates,200);
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
		$messages = UserRate::getMessages();
        $rules = UserRate::getRules();

        $v = Validator::make($request->all(),$rules,$messages);

        $v->sometimes('task_branch_id', 'required|exists:task_branches,id', function($input){
            return $input->type == 'task';
        });

        $v->sometimes('branch_id', 'required|exists:branches,id', function($input){
            return $input->type == 'other';
        });

        $v->sometimes('user_id', 'required|exists:users,id', function($input){
            return $input->type == 'other';
        });

        if($v->fails()){
            $response = ['error' => $v->errors(), 'message' => 'Bad request', 'code' =>  400];
            return response()->json($response,400);
        }

        $rate = new UserRate;
        $userRequested = \Auth::User();
        if($request->input('type') == 'other'){
            $branch = Branch::where(['id' => $request->input('branch_id')])->first();
            if($userRequested->id != $branch->company->user->id){
                $response = ['error' => 'Unauthorized',
                            'code' => 403];
                \Log::error(sprintf("User: %s requested add a user rate with branch dont own: %s. Real owner: %s",
                                    $userRequested->id, $request->input('branch_id'), $branch->company->id));
                return response()->json($response,403);
            }
            $rate->branch_id  = $request->input('branch_id');
            $rate->user_id = $request->input('user_id');
        }elseif($request->input('type') == 'task'){
            $taskBranch = TaskBranch::where(['id' => $request->input('task_branch_id')])->first();
            if($userRequested->id != $taskBranch->branch->company->user->id){
                $response = ['error' => 'Unauthorized',
                            'code' => 403];
                \Log::error(sprintf("User: %s requested add a user rate with task branch dont own: %s. Real owner: %s",
                                    $userRequested->id, $request->input('task_branch_id'), $taskBranch->branch->company->user->id));
                return response()->json($response,403);
            }

            $findRate = UserRate::where('object_id', $taskBranch->task->id)
                                ->where('object_type', UserRate::USER_RATE_OBJECTS_MAP['Task'])
                                ->where('branch_id', $taskBranch->branch_id)
                                ->first();
            if($findRate){
                $response = ['error' => "Ya calificaste al usuario de esta tarea", 'code' =>  422];
                return response()->json($response,422);
            }

            $rate->object_id = $taskBranch->task->id;
            $rate->object_type = UserRate::USER_RATE_OBJECTS_MAP['Task'];
            $rate->branch_id = $taskBranch->branch_id;
            $rate->user_id = $taskBranch->task->user->id;
        }


        if($userRequested->id == $request->input('user_id')){
            $response = ['error' => "Can't rate yourself", 'code' =>  422];
            return response()->json($response,422);
        }


        $attributes = [     'rate'
                            , 'comment'
                        ];
        $this->updateModel($request, $rate, $attributes);
        $saved = $rate->save();
		//SE VALIDA QUE EL SERVICE EXISTA
		if($saved){
			$response = ['data' => $rate,'code' => 200,'message' => 'Rate was registered succefully'];
			return response()->json($response,200);
		}else{
			//EN DADO CASO QUE EL ID DEL SERVICE NO SE HALLA ENCONTRADO
			$response = ['error' => 'It has occurred an error trying to set the rate','code' => 500];
			return response()->json($response,500);
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
        $rate = UserRate::find($id);

		if(!is_null($rate)){

			$response = ['data' => $rate,'code' => 200];
			return response()->json($response,200);

		}else{
			$response = ['error' => 'Rate does no exist','code' => 422];
			return response()->json($response,422);
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
        $messages = UserRate::getMessages();
		$validation = UserRate::getValidations();

		$v = Validator::make($request->all(),$validation,$messages);

		//SE VERIFICA SI ALGUN CAMPO NO ESTA CORRECTO
		if($v->fails()){
			$response = ['error' => 'Bad Request', 'data' => $v->messages(), 'code' =>  422];
			return response()->json($response,422);
		}

		$rate = UserRate::find($id);
		if(!is_null($rate)){
			$fields = ['rate' => $request->rate,'comment' => $request->comment];

			$row = UserRate::where('id','=',$id)->update($fields);

			//SE VALIDA QUE SE HALLA ACTUALIZADO EL REGISTRO
			if($row != false){
				$response = ['data' => $rate,'code' => 200,'message' => 'Rate was updated succefully'];
				return response()->json($response,200);
			}else{
				$response = ['error' => 'It has occurred an error trying to update the rate','code' => 500];
				return response()->json($response,500);
			}

		}else{

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
        $rate = UserRate::find($id);

		if(!is_null($rate)){

			//SE BORRA EL RATE
			$row = $rate->delete();

			if($row != false){
				$response = ['code' => 200,'message' => "Rate was deleted succefully"];
				return response()->json($response,200);
			}else{
				$response = ['error' => 'It has occurred an error trying to delete the rate','code' => 404];
				return response()->json($response,404);
			}

		}else{
			//EN DADO CASO QUE EL ID DEL RATE NO SE HALLA ENCONTRADO
			$response = ['error' => 'Rate does not exist','code' => 422];
			return response()->json($response,422);
		}
    }
}

<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\BranchRate;
use JWTAuth;
use Validator;
use App\Service;
use App\Branch;
use App\TaskBranch;
use App\User;
use App\Company;

class BranchRateController extends Controller
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
        $rates = BranchRate::with('tasks')->get();
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
        $messages = BranchRate::getMessages();
        $rules = BranchRate::getRules();

        $v = Validator::make($request->all(),$rules,$messages);

        $v->sometimes('task_branch_id', 'required|exists:task_branches,id', function($input){
            return $input->type == 'task';
        });

        $v->sometimes('branch_id', 'required|exists:branches,id', function($input){
            return $input->type == 'other';
        });

        if($v->fails()){
            $response = ['error' => $v->errors(), 'message' => 'Bad request', 'code' =>  400];
            return response()->json($response,400);
        }

        $userRequested = \Auth::User();
        $rate = new BranchRate;
        $rate->user_id = $userRequested->id;

        if($request->input('type') == 'other'){
            $branch = Branch::where(['id' => $request->input('branch_id')])->first();
            $findRate = BranchRate::where('object_id', null)
                                ->where('object_type', null)
                                ->where('branch_id', $request->input('branch_id'))
                                ->where('user_id', $userRequested->id)
                                ->first();
            if($findRate){
                $response = ['error' => "Ya calificaste al negocio", 'code' =>  422];
                return response()->json($response,422);
            }
            $rate->branch_id  = $request->input('branch_id');

        }elseif($request->input('type') == 'task'){
            $taskBranch = TaskBranch::where(['id' => $request->input('task_branch_id')])->first();
            $branch = $taskBranch->branch;
            if($userRequested->id != $taskBranch->task->user_id){
                $response = ['error' => 'Unauthorized',
                            'code' => 403];
                \Log::error(sprintf("User: %s requested add a branch rate with task branch dont own: %s. Real owner: %s",
                                    $userRequested->id, $request->input('task_branch_id'), $taskBranch->task->user_id));
                return response()->json($response,403);
            }

            $findRate = BranchRate::where('object_id', $taskBranch->task->id)
                                ->where('object_type', BranchRate::BRANCH_RATE_OBJECTS_MAP['Task'])
                                ->where('branch_id', $taskBranch->branch_id)
                                ->first();
            if($findRate){
                $response = ['error' => "Ya calificaste al negocio de este proyecto", 'code' =>  422];
                return response()->json($response,422);
            }

            $rate->object_id = $taskBranch->task->id;
            $rate->object_type = BranchRate::BRANCH_RATE_OBJECTS_MAP['Task'];
            $rate->branch_id = $taskBranch->branch_id;
            $rate->user_id = $userRequested->id;
        }


        if($userRequested->id == $branch->company->user->id){
            $response = ['error' => "Can't rate your own business", 'code' =>  422];
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
        $rate = BranchRate::find($id);

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
        $messages = BranchRate::getMessages();
        $validation = BranchRate::getValidations();

        $v = Validator::make($request->all(),$validation,$messages);

        //SE VERIFICA SI ALGUN CAMPO NO ESTA CORRECTO
        if($v->fails()){
            $response = ['error' => 'Bad Request', 'data' => $v->messages(), 'code' =>  422];
            return response()->json($response,422);
        }

        $rate = BranchRate::find($id);
        if(!is_null($rate)){
            $fields = ['rate' => $request->rate,'comment' => $request->comment];

            $row = BranchRate::where('id','=',$id)->update($fields);

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
        $rate = BranchRate::find($id);

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

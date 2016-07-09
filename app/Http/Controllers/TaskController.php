<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Task;
use App\TaskImage;
use App\TaskBranch;
use App\Mailers\AppMailer;
use Validator;
use JWTAuth;
use App\Extensions\Utils;
use App\Jobs\SendFunctionJob;

use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;

class TaskController extends Controller
{
    public function __construct(){
        $this->middleware('jwt.auth:user', ['only' => ['update', 'store']]);
        $this->middleware('default.headers');
        $this->userTypes = \Config::get('app.user_types');
        $this->mailer = new AppMailer();
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
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
        $messages = Task::getMessages();
        $rules = Task::getRules();

        $v = Validator::make($request->all(),$rules,$messages);

        if($v->fails()){
            $response = ['error' => $v->errors(), 'message' => 'Bad request', 'code' =>  400];
            return response()->json($response,400);
        }

        $userRequested = \Auth::User();
        $task = new Task;
        $attributes = ['category_id'
                            , 'description'
                            , 'delivery_service'
                            , 'date'
                            , 'latitude'
                            , 'longitude'
                        ];
        $this->updateModel($request, $task, $attributes);
        $task->user_id = $userRequested->id;
        $task->status = 0; //it means the task is open
        $task->geom = [$request->longitude, $request->latitude];
        if($task->save()){
            $this->sendTaskToBranches($task);
            $tokenImage = \Crypt::encrypt(['task_id' => $task->id
                                                ,'date' => $task->created_at->format('Y-m-d H:i:s')]);
            $task->token_image = $tokenImage;
            $response = ['data' => $task,'code' => 200,'message' => 'Task was created succefully'];
            return response()->json($response,200);
        }
        $response = ['error' => 'It has occurred an error trying to save the branch','code' => 500];
        return response()->json($response,500);
    }


    public function setImages(Request $request, $taskId){

        $resp = $this->validateImageToken($taskId, $request->header('X-Task-Token'));
        if($resp['code'] > 200){
            return response()->json($resp, $resp['code']);
        }
        $task = Task::find($taskId);
        if(!$task){
            $response = ['ext' => $ext, 'error' => "No existe la tarea", 'code' =>  404];
            return response()->json($response,404);
        }

        $ext = $request->file('image')->getClientOriginalExtension();
        // Se verifica si es un formato de imagen permitido
        if($ext !='jpg' && $ext !='jpeg' && $ext !='bmp' && $ext !='png'){
            $response = ['ext' => $ext, 'error' => "Sólo imágenes de extensión jpg, jpeg, bmp and png", 'code' =>  422];
            return response()->json($response,422);
        }
        $img = Utils::StorageImage($taskId,$request->file('image'), 'tasks/images/', 'tasks/thumbs/');
        $taskImage = new TaskImage();
        $taskImage->image = $img['image'];
        $taskImage->thumbnail = $img['thumbnail'];

        $saveImage = $task->images()->save($taskImage);

        if($saveImage != false){
            $response = ['code' => 200, 'data' => $saveImage, 'message' => 'Image was save succefully'];
            return response()->json($response,200);
        }else{
            $response = ['error' => 'It has occurred an error trying to update the task image','code' => 500];
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
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }


    private function validateImageToken($taskId, $headerToken){
        try{
            $imageToken = \Crypt::decrypt($headerToken);
        }catch(\Illuminate\Contracts\Encryption\DecryptException $e){
            $data = ['token' => ["El token no es válido"]];
            $response = ['data' => $data, 'error' => 'Bad request', 'code' => 403];
            return $response;
        }

        $tokenTaskId = $imageToken['task_id'];
        $tokenDate = $imageToken['date'];
        if($tokenTaskId != $taskId){
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

    private function sendTaskToBranches($task){
        $branches = $task->getNeareastBranches();
        foreach ($branches as $key => $branch) {
            $taskBranch = new TaskBranch;
            $taskBranch->task_id = $task->id;
            $taskBranch->branch_id = $branch->id;
            $taskBranch->status = 0;
            $taskBranch->save();
        }
    }
}
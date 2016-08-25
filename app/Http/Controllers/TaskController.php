<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Task;
use App\Branch;
use App\Company;
use App\TaskImage;
use App\TaskBranch;
use App\TaskBranchQuote;
use Validator;
use JWTAuth;
use App\Extensions\Utils;
use App\Jobs\SendFunctionJob;

use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;

class TaskController extends Controller
{
    public function __construct(){
        parent::__construct();
        $this->middleware('jwt.auth:user', ['only' => ['index',
                                                        'indexBranch',
                                                        'indexCompany',
                                                        'show',
                                                        'update',
                                                        'store',
                                                        'storeQuote',
                                                        'showTaskBranch',
                                                        'updateTaskBranch',
                                                        ]]);
        $this->middleware('default.headers');
        $this->userTypes = \Config::get('app.user_types');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $user = \Auth::User();
        $tasks = [];
        $tasks = Task::with('category')
                        ->with('imagesHidden')
                        ->with('quotes')
                        ->with('distanceBranches.branch.company')
                        ->searchBy($request)
                        ->betweenBy($request)
                        ->orderByCustom($request)
                        ->limit($request)
                        ->where('tasks.user_id', $user->id)
                        ->get();

        return response()->json(['data'=>$tasks], 200);
    }

    public function indexBranch(Request $request, $branchId){
        $user = \Auth::User();
        $tasks = [];
        $branch = Branch::where('id', $branchId)
                ->with('company.user')
                ->whereHas('company.user', function($query) use ($user){
                    $query->where('id', $user->id);
                })
                ->first();
        if(!$branch){
            $response = ['error' => 'Resource not found','code' => 404];
            return response()->json($response,404);
        }

        $tasks = TaskBranch::where('branch_id', $branchId)
                        ->withDistance()
                        ->with('task.user')
                        ->searchBy($request)
                        ->betweenBy($request)
                        ->orderByCustom($request)
                        ->limit($request)
                        ->get();

        return response()->json(['data'=>$tasks], 200);
    }

	 public function indexCompany(Request $request, $companyId){
        $user = \Auth::User();

		 $company = Company::where('id', $companyId)
                ->first();
        if(!$company){
            $response = ['error' => 'Resource not found','code' => 404];
            return response()->json($response,404);
        }

		// \DB::connection()->enableQueryLog();
        $tasks = TaskBranch::with('branch.company')
						->with('task')
						 ->whereHas('branch.company', function($query) use ($companyId){
									$query->where('id', $companyId);
								})
                        ->searchBy($request)
                        ->betweenBy($request)
                        ->orderByCustom($request)
                        ->limit($request)
                        ->get();
		// $query = \DB::getQueryLog();
		if(!$tasks){
            $response = ['error' => 'Resource not found','code' => 404];
            return response()->json($response,404);
        }
		$count = $tasks->count();
        return response()->json(['count'=>$count,'data'=>$tasks], 200);
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
            $numberBranches = 0;
            if(config('app.automatic_assign')){
                $numberBranches = $this->sendTaskToBranches($task);
            }else{
                //we send an email to the admin with the new task information
                $view = \View::make('emails.admin-new-task', ['task' => $task]);
                $content = $view->render();
                $this->sendAdminNotificationEmail('Nueva tarea', $content);
            }

            $tokenImage = \Crypt::encrypt(['task_id' => $task->id
                                                ,'date' => $task->created_at->format('Y-m-d H:i:s')]);
            $task->token_image = $tokenImage;
            $task->branches_sent = $numberBranches;
            $response = ['data' => $task,'code' => 200,'message' => 'Task was created succefully'];
            return response()->json($response,200);
        }
        $response = ['error' => 'It has occurred an error trying to save the task','code' => 500];
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

    public function storeQuote(Request $request, $taskId){
        $messages = TaskBranchQuote::getMessages();
        $rules = TaskBranchQuote::getRules();

        $v = Validator::make($request->all(),$rules,$messages);

        if($v->fails()){
            $response = ['error' => $v->errors(), 'message' => 'Bad request', 'code' =>  400];
            return response()->json($response,400);
        }
        $taskBranchId = $request->input('task_branch_id');
        $taskBranch = TaskBranch::find($taskBranchId);

        $userRequested = \Auth::User();
        $userBranch = $taskBranch->getOwnerBranch();
        //If the user is not the owner of the branch, then return a 403
        if($userRequested->id != $userBranch->id){
            $response = ['error' => 'Unauthorized',
                        'code' => 403];
            \Log::error(sprintf("User: %s requested add a quote for task: %s with task branch dont own: %s. Real owner: %s",
                                $userRequested->id, $taskId, $taskBranchId, $userBranch->id));
            return response()->json($response,403);
        }

        //check that the task id in the url matches the task_id from task_branch_id
        if($taskBranch->task_id != $taskId){
            $response = ['error' => 'Unauthorized',
                        'code' => 403];
            \Log::error(sprintf("User: %s requested add a quote for task: %s with task branch: %s. the taskes dont mach: Real task %s",
                                $userRequested->id, $taskId, $taskBranchId, $taskBranch->task_id));
            return response()->json($response,403);
        }

        $quote = new TaskBranchQuote;
        $quote->description = $request->input('description');
        $quote->price = $request->input('price');

        $savedQuote = $taskBranch->quotes()->save($quote);
        if($savedQuote){
            $this->sendTaskQuoteEmail($taskBranch, $savedQuote);
            $response = ['data' => $savedQuote,'code' => 200,'message' => 'Quote was created succefully'];
            return response()->json($response,200);
        }
        $response = ['error' => 'It has occurred an error trying to save the quote','code' => 500];
        return response()->json($response,500);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $user = \Auth::User();

        $task = Task::with('category')
                        ->with('distanceBranches.branch.company')
                        ->where('id', $id)
                        ->where('user_id', $user->id)
                        ->first();

        if(!is_null($task)){

            $response = ['code' => 200,'data' => $task];
            return response()->json($response,200);

        }else{
            $response = ['error' => 'Resource not found','code' => 404];
            return response()->json($response,404);
        }

        return response()->json(['data'=>$tasks], 200);
    }

    public function showTaskBranch($taskId, $taskBranchId){
        $userRequested = \Auth::User();
        $taskBranch = $this->validateTaskBranchOwner($userRequested, $taskId, $taskBranchId, true);
        if($taskBranch instanceof JsonResponse){
            return $taskBranch;
        }

        $response = ['data' => $taskBranch,'code' => 200,'code' => 200];
        return response()->json($response,200);
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

    public function updateTaskBranch(Request $request, $taskId, $taskBranchId){
        $userRequested = \Auth::User();

        $taskBranch = $this->validateTaskBranchOwner($userRequested, $taskId, $taskBranchId);
        if($taskBranch instanceof JsonResponse){
            return $taskBranch;
        }
        $messages = TaskBranch::getUpdateMessages();
        $rules = TaskBranch::getUpdateRules();

        $v = Validator::make($request->all(),$rules,$messages);

        if($v->fails()){
            $response = ['error' => $v->errors(), 'message' => 'Bad request', 'code' =>  400];
            return response()->json($response,400);
        }

        if($request->input('status'))
            $taskBranch->status = $request->input('status');

        if($taskBranch->save()){
            $response = ['data' => $taskBranch,'code' => 200,'code' => 200];
            return response()->json($response,200);
        }else{
            $response = ['error' => 'It has occurred an error trying to update the task branch relationship','code' => 500];
            return response()->json($response,500);
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
        //
    }

    private function validateTaskBranchOwner($userRequested, $taskId, $taskBranchId, $ownerTask=false){
        $taskBranch = TaskBranch::withDistance()
                ->where(['task_branches.id' => $taskBranchId])
                ->with(['task' => function($q){
                    $q->with('images');
                    $q->with('userHidden');
                }])
                ->with('quotes')
                ->with('branch.tags')
                ->first();

        if(!$taskBranch){
            $response = ['error' => 'Task Branck relationship does not exist','code' => 404];
            return response()->json($response,404);
        }

        if($taskId != $taskBranch->task_id){
            $response = ['error' => 'The task is not related to the relationship',
                        'code' => 403];
            \Log::error(sprintf("User: %s requested update task branch from branch id: %s. the original task %s doesn't match with: %s",
                                $userRequested->id, $taskBranchId, $taskBranch->task_id, $taskId));
            return response()->json($response, 403);
        }

        if($ownerTask && $userRequested->id == $taskBranch->task->user_id){
            return $taskBranch;
        }

        //check if the user requesting it, is the owner of the branch
        if($userRequested->id != $taskBranch->branch->company->user->id){
            $response = ['error' => 'Unauthorized',
                        'code' => 403];
            \Log::error(sprintf("User: %s requested get task branch from taskbranch id: %s. He doesn't own that branch",
                                $userRequested->id, $taskBranchId));
            return response()->json($response, 403);
        }

        return $taskBranch;
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
        //first we save the branches that we found were close to the task
        foreach ($branches as $key => $branch) {
            $taskBranch = new TaskBranch;
            $taskBranch->branch_id = $branch->id;
            $taskBranch->status = 0;
            $task->branches()->save($taskBranch);
        }
        //then we get the branches branch, company and user information
        $taskDetail = Task::where(['id' => $task->id])->with('branches.branch.company.user')->first();
        $branchesTask = $taskDetail['branches'];
        foreach ($branchesTask as $key => $branchTask) {

            $branch = $branchTask['branch'];
            $company = $branch['company'];
            $user = isset($company['user']) ? $company['user'] : null;
            $branchEmail =  isset($user) ? $user['email'] : $branch['email'];
            $branchName = $company['name'];

            if(isset($branchEmail)){
                $this->mailer->pushToQueue('sendNewTaskEmail', [
                    'baseUrl' => $this->baseUrl,
                    'category' => $task->category->name,
                    'userName' => $task->user->name,
                    'date' => $task->date,
                    'taskBranchId' => $branchTask['id'],
                    'branch_email' => $branchEmail,
                    'branch_name' => $branchName,
                    'description' => $task->description
                ]);
            }
            //for each branch, we send an email saying that the task might be interesting for them

        }
        return count($branchesTask);
    }

    public function sendTaskQuoteEmail($taskBranch, $quote){
        $task = $taskBranch->task;
        $user = $task->user;
        $branch = Branch::where(['id' => $taskBranch->branch_id])->with('company.user')->first();

        $this->mailer->pushToQueue('sendNewTaskQuoteEmail', [
            'baseUrl' => $this->baseUrl,
            'taskDescription' => $task->description,
            'taskDate' => $task->date,
            'quotePrice' => number_format($quote->price, 2),
            'quoteDescription' => $quote->description,
            'quoteId' => $quote->id,
            'taskId' => $task->id,
            'taskBranchId' => $taskBranch->id,
            'user_email' => $user->email,
            'user_name' => $user->name,
            'branchName' => $branch->company->name
        ]);
    }
}
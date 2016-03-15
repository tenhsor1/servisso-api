<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\NewComment;
use Validator;
use JWTAuth;
use App\Http\Controllers\Controller;

class NewCommentController extends Controller
{
    public function __construct(){
        $this->middleware('jwt.auth:admin|partner', ['only' => ['store','update','destroy']]);
		$this->UserRoles = \Config::get('app.user_roles');
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
      	$userRequested = \Auth::User();
		  $comment = NewComment::searchBy($request)
                                ->betweenBy($request)
                                ->orderByCustom($request)
                                ->limit($request)
                                ->get();
		$count = $comment->count();
		 if(!is_null($comment)){
            $response = ['code' => 200,'Count' => $count,'data' => $comment];
            return response()->json($response,200);
        }else{
            $response = ['error' => 'Comment are empty','code' => 404];
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

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
		$messages = NewComment::getMessages();
        $validation = NewComment::getValidations();
		$userRequested = \Auth::User();
        $v = Validator::make($request->all(),$validation,$messages);
        //SE VERIFICA SI ALGUN CAMPO NO ESTA CORRECTO
        if($v->fails()){
            $response = ['error' => 'Bad Request', 'data' => $v->messages(), 'code' =>  422];
			return response()->json($response,422);
        }

			$comment = new NewComment;
            $comment->news_id = $request->news_id;
            $comment->user_id = $userRequested->id;
            $comment->comment = $request->comment;
            $comment->user_type = $this->UserRoles[$userRequested->roleAuth];
			$comment->role_id = $userRequested->id;//id de quien modifico
            $comment->role = $this->UserRoles[$userRequested->roleAuth];//rol de quien modifico
            $row= $comment->save();

        if($row != false){
            $response = ['code' => 200,'message' => 'News was created succefully'];
            return response()->json($response,200);
        }else{
            $response = ['error' => 'It has occurred an error trying to save the news','code' => 404];
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
        $comment = NewComment::find($id);
        if(!is_null($comment)){
            $response = ['code' => 200,'data' => $comment];
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
		$comment = NewComment::find($id);
        if(!is_null($comment)){
			$userRequested = \Auth::User();
			if($userRequested->id == $comment->user_id || $userRequested->roleAuth  == "ADMIN"){
				$messages = NewComment::getMessages();
                $validation = NewComment::getValidations();
                $v = Validator::make($request->all(),$validation,$messages);
                //SE VERIFICA SI ALGUN CAMPO NO ESTA CORRECTO
                if($v->fails()){
                    $response = ['error' => 'Bad Request', 'data' => $v->messages(),'code' => 422];
                    return response()->json($response,422);
                }

                $comment->news_id = $request->news_id;
				$comment->user_id = $userRequested->id;
				$comment->comment = $request->comment;
				$comment->user_type = $this->UserRoles[$userRequested->roleAuth];
				$comment->role_id = $userRequested->id;//id de quien modifico
				$comment->role = $this->UserRoles[$userRequested->roleAuth];//rol de quien modifico
                $row = $comment->save();
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
		$comment = NewComment::find($id);
        if(!is_null($comment)){
			$userRequested = \Auth::User();
			if($userRequested->id == $comment->user_id || $userRequested->roleAuth  == "ADMIN"){
					$comment->role_id = $userRequested->id;//id de quien modifico
					$comment->role = $this->UserRoles[$userRequested->roleAuth];//rol de quien modifico
					$comment->save();
					$rows = $comment->delete();
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

}

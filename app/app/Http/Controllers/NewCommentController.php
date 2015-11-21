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
        $this->middleware('jwt.auth:admin|user', ['only' => ['store','update','destroy']]);
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $news = NewComment::all();
        if(!is_null($news)){
            $response = ['code' => 200,'data' => $news];
            return response()->json($response,200,[],JSON_PRETTY_PRINT);
        }else{
            $response = ['error' => 'Admin are empty','code' => 404];
            return response()->json($response,404,[],JSON_PRETTY_PRINT);
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
            $response = ['error' => $v->messages(), 'code' =>  406];
            return response()->json($response,460,[],JSON_PRETTY_PRINT);
        }

          $new = new NewComment;
            $new->news_id = $request->news_id;
            $new->user_id = $userRequested->id;
            $new->comment = $request->comment;
            $new->user_type = $request->user_type;

            $row= $new->save();

        if($row != false){
            $response = ['code' => 200,'message' => 'News was created succefully'];
            return response()->json($response,200,[],JSON_PRETTY_PRINT);
        }else{
            $response = ['error' => 'It has occurred an error trying to save the news','code' => 404];
            return response()->json($response,404,[],JSON_PRETTY_PRINT);
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
        $news = NewComment::find($id);
        if(!is_null($news)){
            $response = ['code' => 200,'data' => $news];
            return response()->json($response,200,[],JSON_PRETTY_PRINT);
        }else{
            $response = ['error' => 'Comment does no exist','code' => 404];
            return response()->json($response,404,[],JSON_PRETTY_PRINT);
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
		$new = NewComment::find($id);
        if(!is_null($new)){
			$userRequested = \Auth::User();
			if($userRequested->id == $new->user_id){
				$messages = NewComment::getMessages();
                $validation = NewComment::getValidations();
                $v = Validator::make($request->all(),$validation,$messages);
                //SE VERIFICA SI ALGUN CAMPO NO ESTA CORRECTO
                if($v->fails()){
                    $response = ['error' => $v->messages(),'code' => 422];
                    return response()->json($response,404,[],JSON_PRETTY_PRINT);
                }

                $new->news_id = $request->news_id;
				$new->user_id = $userRequested->id;
				$new->comment = $request->comment;
				$new->user_type = $request->user_type;
                $row = $new->save();
                if($row != false){
                    $response = ['code' => 200,'message' => 'Comment was update succefully'];
					return response()->json($response,200,[],JSON_PRETTY_PRINT);
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
		$new = NewComment::find($id);
        if(!is_null($new)){
			$userRequested = \Auth::User();
			if($userRequested->id == $new->user_id || $userRequested->roleAuth  == "ADMIN"){
					$rows = $new->delete();
					if($rows > 0){
						$response = ['code' => 200,'message' => "Comment was deleted succefully"];
						return response()->json($response,200,[],JSON_PRETTY_PRINT);
					}else{
						$response = ['error' => 'It has occurred an error trying to delete the Comment','code' => 404];
						return response()->json($response,404,[],JSON_PRETTY_PRINT);
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

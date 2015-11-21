<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\News;
use App\Admin;
use Validator;
use App\NewComment;
use JWTAuth;
class NewController extends Controller
{

    public function __construct(){
        $this->middleware('jwt.auth:admin|user', ['only' => ['update','store','destroy']]);
		$this->super = \Config::get('app.super_admin');
	}
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {

        $news = News::all();
        if(!is_null($news)){
            $response = ['code' => 200,'data' => $news];
            return response()->json($response,200,[],JSON_PRETTY_PRINT);
        }else{
            $response = ['error' => 'News are empty','code' => 404];
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
     * @param Request|Requests\NewStoreRequest $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $adminRequested = \Auth::User();
		//SE VALIDA QUE EL USUARIO SEA DE TIPO ADMIN Y QUE EL ID DEL ADMIN LE PERTENEZCA A QUIEN CREA LA NOTICIA
        if($adminRequested->roleAuth  == "ADMIN"){
        $messages = News::getMessages();
        $validation = News::getValidations();
        $v = Validator::make($request->all(),$validation,$messages);
        //SE VERIFICA SI ALGUN CAMPO NO ESTA CORRECTO
        if($v->fails()){
            $response = ['error' => $v->messages(), 'code' =>  460];
            return response()->json($response,460,[],JSON_PRETTY_PRINT);
        }

            $new = new News;
            $new->admin_id = $adminRequested->id;
            $new->title = $request->title;
            $new->content = $request->content;
            $new->image = $request->image;
            $new->status = $request->status;

            $row= $new->save();

        if($row != false){
            $response = ['code' => 200,'message' => 'News was created succefully'];
            return response()->json($response,200,[],JSON_PRETTY_PRINT);
        }else{
            $response = ['error' => 'It has occurred an error trying to save the news','code' => 404];
            return response()->json($response,404,[],JSON_PRETTY_PRINT);
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
        $news = News::with('comments')->where('id','=',$id)->get();
		//$news = News::find($id);
        if(!is_null($news)){
            $response = ['code' => 200,'data' => $news];
            return response()->json($response,200,[],JSON_PRETTY_PRINT);
        }else{
            $response = ['error' => 'News does no exist','code' => 404];
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


        $new = News::find($id);

        if(!is_null($new)){

            $adminRequested = \Auth::User();//quien hizo la peticion

            if(($adminRequested->roleAuth  == "ADMIN" && $adminRequested->id == $id) ||
               ($adminRequested->roleAuth  == "ADMIN" && $adminRequested->role_id == $this->super)){ //se valida quien mando la peticion le pertenecen sus datos

                $messages = News::getMessages();
                $validation = News::getValidations();
                $v = Validator::make($request->all(),$validation,$messages);
                //SE VERIFICA SI ALGUN CAMPO NO ESTA CORRECTO
                if($v->fails()){
                    $response = ['error' => $v->messages(),'code' => 422];
                    return response()->json($response,404,[],JSON_PRETTY_PRINT);
                }

                $new->admin_id = $request->admin_id;
                $new->title = $request->title;
                $new->content = $request->content;
                $new->image = $request->image;
                $new->status = $request->status;
                $row = $new->save();
                if($row != false){
                    $response = ['code' => 200,'message' => 'News was update succefully'];
					return response()->json($response,200,[],JSON_PRETTY_PRINT);
                }else{
                    $response = ['error' => 'It has occurred an error trying to update the news','code' => 404];
                    return response()->json($response,404);
                }


            }else{
                //EN DADO CASO QUE EL ID DE NO SEA UN ADMINISTRADOR
                $response = ['error' => 'Unauthorized','code' => 403];
                return response()->json($response,403);
            }

        }else{
            //EN DADO CASO QUE EL ID DEL ADMIN NO SE HALLA ENCONTRADO
            $response = ['error' => 'News does not exist','code' => '404'];
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
        $news = News::find($id);
        if(!is_null($news)){
            $adminRequested = \Auth::User();//quien hizo la peticion
			 if($adminRequested->roleAuth  == "ADMIN"){
                $rows = $news->delete();
                if($rows > 0){
                    $response = ['code' => 200,'message' => "News was deleted succefully"];
                    return response()->json($response,200,[],JSON_PRETTY_PRINT);
                }else{
                    $response = ['error' => 'It has occurred an error trying to delete the news','code' => 404];
                    return response()->json($response,404,[],JSON_PRETTY_PRINT);
                }
            }else{
                //EN DADO CASO QUE EL ID DE NEWS NO LE PERTENEZCA
                $response = ['error' => 'Unauthorized','code' => 403];
                return response()->json($response,403);
            }
        }else{
            //EN DADO CASO QUE EL ID DEL ADMIN NO SE HALLA ENCONTRADO
            $response = ['error' => 'News does not exist','code' => '404'];
            return response()->json($response,404);
        }
    }



}

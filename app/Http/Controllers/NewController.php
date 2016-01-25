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
        $this->middleware('jwt.auth:admin|partner', ['only' => ['update','store','destroy']]);
		$this->UserRoles = \Config::get('app.user_roles');
	}
    /**  
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {

        // $news = News::with('admin')->get();
		$userRequested = \Auth::User();
		  $news = News::with('admin')
						->searchBy($request)
						->betweenBy($request)
						->orderByCustom($request)
						->limit($request)
						->get();
		$count = $news->count();  
		if(!is_null($news)){
            $response = ['code' => 200,'Count' => $count,'data' => $news];
            return response()->json($response,200);
        }else{
            $response = ['error' => 'News are empty','code' => 404];
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
            return response()->json($response,460);
        }

            $new = new News;
            $new->admin_id = $adminRequested->id;
            $new->title = $request->title;
            $new->content = $request->content;
            $new->image = $request->image;
            $new->status = $request->status;
			$new->role_id = $adminRequested->id;//id de quien modifico
            $new->role = $this->UserRoles[$adminRequested->roleAuth];//rol de quien modifico
            $row= $new->save();

        if($row != false){
            $response = ['code' => 200,'message' => 'News was created succefully'];
            return response()->json($response,200);
        }else{
            $response = ['error' => 'It has occurred an error trying to save the news','code' => 404];
            return response()->json($response,404);
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
        $news = News::with('admin','comments')->where('id','=',$id)->get();
        if(!is_null($news)){
            $response = ['code' => 200,'data' => $news];
            return response()->json($response,200);
        }else{
            $response = ['error' => 'News does no exist','code' => 404];
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


        $new = News::find($id);

        if(!is_null($new)){

            $adminRequested = \Auth::User();//quien hizo la peticion

            if($adminRequested->roleAuth  == "ADMIN"){ //se valida quien mando la peticion le pertenecen sus datos

                $messages = News::getMessages();
                $validation = News::getValidations();
                $v = Validator::make($request->all(),$validation,$messages);
                //SE VERIFICA SI ALGUN CAMPO NO ESTA CORRECTO
                if($v->fails()){
                    $response = ['error' => $v->messages(),'code' => 422];
                    return response()->json($response,404);
                }

                  
                $new->title = $request->title;
                $new->content = $request->content;
                $new->image = $request->image;
                $new->status = $request->status;
                $new->role_id = $adminRequested->id;//id de quien modifico
                $new->role = $this->UserRoles[$adminRequested->roleAuth];//rol de quien modifico
                $row = $new->save();
                if($row != false){
                    $response = ['code' => 200,'message' => 'News was update succefully'];
					return response()->json($response,200);
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
				$news->role_id = $adminRequested->id;//id de quien modifico
                $news->role = $this->UserRoles[$adminRequested->roleAuth];//rol de quien modifico
                $news->save(); 
                $rows = $news->delete();  
				if($rows > 0){
                    $response = ['code' => 200,'message' => "News was deleted succefully"];
                    return response()->json($response,200);
                }else{
                    $response = ['error' => 'It has occurred an error trying to delete the news','code' => 404];
                    return response()->json($response,404);
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

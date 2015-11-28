<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;
Use App\Category;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
class CategoryController extends Controller
{
	public function __construct(){
        $this->middleware('jwt.auth:admin', ['only' => ['update','store','destroy']]);
		$this->UserRoles = \Config::get('app.user_roles'); 
	}
    /**    
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
		//$categories = Category::all();
		// \DB::connection()->enableQueryLog(); 
		  $categories = Category::searchBy($request)
                                ->betweenBy($request)
                                ->orderByCustom($request)
                                ->limit($request)
                                ->get();
		$count = $categories->count();    
		// $query = \DB::getQueryLog();
		if(!is_null($categories)){
			$response = ['code' => 200,'Count' => $count,'data' => $categories];
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
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)

    {       

		$adminRequested = \Auth::User();//quien hizo la peticion
        if($adminRequested->roleAuth  == "ADMIN"){ //se valida quien mando la peticion es un admin
		$messages = Category::getMessages();
        $validation = Category::getValidations();

		
		$v = Validator::make($request->all(),$validation,$messages);	
		
		if($v->fails()){
			$response = ['error' => 'Bad request','data' => $v->messages() ,'code' =>  422];
			return response()->json($response,422);
		}    
		
			//SE CREA UNA INSTANCIA DE CATEGORY
			$category = new Category;
			$category->name = $request->name;
			$category->description = $request->description;
			$category->role_id = $adminRequested->id;//id de quien modifico
            $category->role = $this->UserRoles[$adminRequested->roleAuth];//rol de quien modifico
			
			$row = $category->save();
			
			if($row != false){
				$response = ['data' => $category,'code' => 200];
				return response()->json($response,200);
			}else{
				$response = ['error' => 'It has occurred an error trying to save the category','code' => 404];
				return response()->json($response,404);
			}	
		}else{
                //EN DADO CASO QUE EL ID DE NO SEA UN ADMINISTRADOR
                $response = ['error' => 'Unauthorized','code' => 403];
                return response()->json($response,403);
            }
		
    }
	
	/**
	* Verb: GET
	* Url: domain/v1/category/{id}/tags
	* Se obtienen todas las tags de una categoria especifica
	*/
	public function categoryTags($id){
		
		$category = Category::with('tags')->where('id','=',$id)->first();
		$response = ['data' => $category,'code' => 200];
		return response()->json($response,200);
		
	}

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $category = Category::find($id);
		if(!is_null($category)){
			
			$response = ['data' => $category,'code' => 200];
			return response()->json($response,200);
			
		}else{
			
			//EN DADO CASO QUE EL ID DE CATEGORY NO SE HALLA ENCONTRADO
			$response = ['error' => 'Category does not exist','code' => 422];
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
		$category = Category::find($id);
		
		if(!is_null($category)){
			
			$messages = Category::getMessages();
			$validation = Category::getValidations();
			
			$v = Validator::make($request->all(),$validation,$messages);	
			    
			if($v->fails()){
				$response = ['error' => 'Bad request','data' => $v->messages() ,'code' =>  422];
				return response()->json($response,422);
			}
			
			$adminRequested = \Auth::User();//quien hizo la peticion
			if($adminRequested->roleAuth  == "ADMIN"){ //se valida quien mando la peticion es un admin
				$category->name = $request->name;
				$category->description = $request->description;
				$category->role_id = $adminRequested->id;//id de quien modifico
				$category->role = $this->UserRoles[$adminRequested->roleAuth];//rol de quien modifico
				
				$row = $category->save();
				
				if($row != false){
					$response = ['data' => $category,'code' => 200];
					return response()->json($response,200);
				}else{
					$response = ['error' => 'It has occurred an error trying to update the category','code' => 404];
					return response()->json($response,404);
				}
			}else{
				//EN DADO CASO QUE EL ID DE NO SEA UN ADMINISTRADOR
				$response = ['error' => 'Unauthorized','code' => 403];
				return response()->json($response,403);
			}
			
		}else{
			//EN DADO CASO QUE EL ID DE CATEGORY NO SE HALLA ENCONTRADO
			$response = ['error' => 'Category does not exist','code' => 422];
			return response()->json($response,422);
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
        $category = Category::find($id);
		if(!is_null($category)){
			$adminRequested = \Auth::User();//quien hizo la peticion
			if($adminRequested->roleAuth  == "ADMIN"){ //se valida quien mando la peticion es un admin
				$category->role_id = $adminRequested->id;//id de quien modifico
                $category->role = $this->UserRoles[$adminRequested->roleAuth];//rol de quien modifico
                $category->save(); 
				//SE BORRA CATEGORY
				$rows = $category->delete();
				
				if($rows > 0){
					$response = ['code' => 200,'message' => "Company was deleted succefully"];
					return response()->json($response,200);
				}else{
					$response = ['error' => 'It has occurred an error trying to delete the category','code' => 404];
					return response()->json($response,404);
				}
			}else{
				//EN DADO CASO QUE EL ID DE NO SEA UN ADMINISTRADOR
				$response = ['error' => 'Unauthorized','code' => 403];
				return response()->json($response,403);
			}			
			
		}else{
			//EN DADO CASO QUE EL ID DE CATEGORY NO SE HALLA ENCONTRADO
			$response = ['error' => 'Category does not exist','code' => 422];
			return response()->json($response,422);  
		}

    } 

}

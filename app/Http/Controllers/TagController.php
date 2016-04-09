<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Tag;
use App\Category;
use Validator;

class TagController extends Controller
{
	public function __construct(){
        $this->middleware('jwt.auth:admin', ['only' => ['update','destroy']]);
        $this->middleware('default.headers');
		$this->user_roles = \Config::get('app.user_roles');
    }
	
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {	
		$tags = Tag::searchBy($request)
					->betweenBy($request)
					->orderByCustom($request)					
                    ->limit($request)
					->get();
								
		$count = $tags->count();
								
		$response = ['count' => $count,'code' => 200,'data' => $tags];
		return response()->json($response,200);
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
	
	public function storeImage(Request $request){
		$random = "test".rand(1,100);
        /*$imageName = $random.".".$request->file('image')->getClientOriginalExtension();
		$request->file('image')->move(base_path() . '/public/images/', $imageName);*/
		
		$path = base_path() . '/public/images/';		
		$file = $request->file('image');
		$imageName = $random.".".$file->getClientOriginalName();
		$image = \Image::make($file);
		$image->save($path.$imageName);
		$image->resize(240,200);
		$image->save($path.'thumb_'.$random.".".$file->getClientOriginalName());
	
		$response = ['count' => 1,'code' => 200,'data' => $imageName];
		return response()->json($response,200);
	}

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $messages = Tag::getMessages();
		$validation = Tag::getValidations();
		
		$v = Validator::make($request->all(),$validation,$messages);	
		
		if($v->fails()){
			$response = ['error' => 'Bad request','data' => $v->messages() ,'code' =>  422];
			return response()->json($response,422);
		}
		
		$category_id = $request->category_id;
		$category = Category::find($category_id);
		
		//SE VERIFICA QUE REALMENTE EXISTA ESA CATEGORIA
		if(!is_null($category)){
			$tag = new Tag;
			$tag->name = $request->name;
			$tag->description = $request->description;
			$tag->category_id = $category->id;

			$row = $tag->save();
			
			if($row != false){
				$response = ['data' => $tag,'code' => 200];
				return response()->json($response,200);
			}else{
				$response = ['error' => 'It has occurred an error trying to save the tag','code' => 500];
				return response()->json($response,500);
			}
			
			
		}else{
			$response = ['error' => 'Bad request','code' =>  422];
			return response()->json($response,422);
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
        $tag = Tag::find($id);
		if(!is_null($tag)){
			
			$response = ['data' => $tag,'code' => 200];
			return response()->json($response,200);
			
		}else{
			
			//EN DADO CASO QUE EL ID DE TAG NO SE HALLA ENCONTRADO
			$response = ['error' => 'Tag does not exist','code' => 422];
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
        $tag = Tag::find($id);
		
		if(!is_null($tag)){
			
			$messages = Tag::getMessages();
			$validation = Tag::getValidations();
			
			$v = Validator::make($request->all(),$validation,$messages);	
			
			if($v->fails()){
				$response = ['error' => 'Bad request','data' => $v->messages() ,'code' =>  422];
				return response()->json($response,422);
			}
			
			$category_id = $request->category_id;
			$category = Category::find($category_id);
			
			//SE VERIFICA QUE REALMENTE EXISTA ESA CATEGORIA
			if(!is_null($category)){
				
				$userRequested = \Auth::User();
				
				if($userRequested->roleAuth == "ADMIN"){
				
					$tag->name = $request->name;
					$tag->description = $request->description;
					$tag->role_id = $userRequested->id;
					$tag->role = $this->user_roles[$userRequested->roleAuth];
					
					$tag->save();
					
					if($tag != false){
						$response = ['data' => $tag,'code' => 200];
						return response()->json($response,200);
					}else{
						$response = ['error' => 'It has occurred an error trying to update the tag','code' => 500];
						return response()->json($response,500);
					}
				}					
				
			}else{
				$response = ['error' => 'Bad request','code' =>  422];
				return response()->json($response,422);
			}				
		}else{
			//EN DADO CASO QUE EL ID DE TAG NO SE HALLA ENCONTRADO
			$response = ['error' => 'Tag does not exist','code' => 422];
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
        $tag = Tag::find($id);
		if(!is_null($tag)){
			
			$userRequested = \Auth::User();
			
			if($userRequested->roleAuth == "ADMIN"){
				
				$tag->role_id = $userRequested->id;
				$tag->role = $this->user_roles[$userRequested->roleAuth];
				$tag->save();
			
				//SE BORRA CATEGORY
				$tag->delete();
				
				if($tag != false){
					$response = ['code' => 200,'message' => "Tag was deleted succefully"];
					return response()->json($response,200);
				}else{
					$response = ['error' => 'It has occurred an error trying to delete the tag','code' => 500];
					return response()->json($response,500);
				}	
			}
		}else{
			//EN DADO CASO QUE EL ID DE Tag NO SE HALLA ENCONTRADO
			$response = ['error' => 'Tag does not exist','code' => 422];
			return response()->json($response,422);
		}
    }
	
}

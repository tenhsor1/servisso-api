<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use Tag;
use Category;

class TagController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $tags = Tag::all();
		$response = ['data' => $tags, 'code' => 200];
		return response()->json($tags,200);
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
        $messages = $this->getMessages();
		$validation = $this->getValidations();
		
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
				return response->json($response,200);
			}else{
				$response = ['error' => 'It has occurred an error trying to save the tag','code' => 404];
				return response()->json($response,404);
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
			
			$messages = $this->getMessages();
			$validation = $this->getValidations();
			
			$v = Validator::make($request->all(),$validation,$messages);	
			
			if($v->fails()){
				$response = ['error' => 'Bad request','data' => $v->messages() ,'code' =>  422];
				return response()->json($response,422);
			}
			
			$category_id = $request->category_id;
			$category = Category::find($category_id);
			
			//SE VERIFICA QUE REALMENTE EXISTA ESA CATEGORIA
			if(!is_null($category)){
				$tag->name = $request->name;
				$tag->description = $request->description;
				$tag->category_id = $category_id;
				
				$row = $tag->save();
				
				if($row != false){
					$response = ['data' => $tag,'code' => 200];
					return resonse()->json($response,200);
				}else{
					$response = ['error' => 'It has occurred an error trying to update the tag','code' => 404];
					return response()->json($response,404);
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
			
			//SE BORRA CATEGORY
			$row = $tag->delete();
			
			if($row != false){
				$response = ['code' => 200,'message' => "Tag was deleted succefully"];
				return response()->json($response,200);
			}else{
				$response = ['error' => 'It has occurred an error trying to delete the tag','code' => 404];
				return response()->json($response,404);
			}	
			
		}else{
			//EN DADO CASO QUE EL ID DE Tag NO SE HALLA ENCONTRADO
			$response = ['error' => 'Tag does not exist','code' => 422];
			return response()->json($response,422);
		}
    }
	
	public function getMessages(){
		$messages = 
		[
			'required' => ':attribute is required',
			'max' => ':attribute length too long',
			'min' => ':attribute length too short',
		];
		
		return $messages;
	}
	
	public function getValidations(){
		$validation = 
			[
				'name' => 'required|max:44|min:3',
				'description' => 'required|max:99|min:4',
				'category_id' 'required'
			];
		
		return $validation;
	}
}

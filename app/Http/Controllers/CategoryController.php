<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;
Use Category;

class CategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $categories = Category::all();
		$response = ['data' => $categories, 'code' => 200];
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
		
		//SE CREA UNA INSTANCIA DE CATEGORY
		$category = new Category;
		$category->name = $request->name;
		$category->description = $request->description;
		
		$row = $category->save();
		
		if($row != false){
			$response = ['data' => $category,'code' => 200];
			return response()->json($response,200);
		}else{
			$response = ['error' => 'It has occurred an error trying to save the category','code' => 404];
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
        $category = Category::find($id);
		if(!is_null($category)){
			
			$response = ['data' => $category,'code' => 200];
			return response()->json($response,200);
			
		}else{
			
			//EN DADO CASO QUE EL ID DE CATEGORY NO SE HALLA ENCONTRADO
			$response = ['error' => 'Company does not exist','code' => 422];
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
			
			$messages = $this->getMessages();
			$validation = $this->getValidations();
			
			$v = Validator::make($request->all(),$validation,$messages);	
			
			if($v->fails()){
				$response = ['error' => 'Bad request','data' => $v->messages() ,'code' =>  422];
				return response()->json($response,422);
			}
			
			$category->name = $request->name;
			$category->description = $request->description;
			
			$row = $category->save();
			
			if($row != false){
				$response = ['data' => $category,'code' => 200];
				return response()->json($response,200);
			}else{
				$response = ['error' => 'It has occurred an error trying to update the category','code' => 404];
				return response()->json($response,404);
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
			
			//SE BORRA CATEGORY
			$rows = $category->delete();
			
			if($rows > 0){
				$response = ['code' => 200,'message' => "Company was deleted succefully"];
				return response()->json($response,200);
			}else{
				$response = ['error' => 'It has occurred an error trying to delete the company','code' => 404];
				return response()->json($response,404);
			}	
			
		}else{
			//EN DADO CASO QUE EL ID DE CATEGORY NO SE HALLA ENCONTRADO
			$response = ['error' => 'Company does not exist','code' => 422];
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
			];
		
		return $validation;
	}
}

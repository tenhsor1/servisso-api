<?php

namespace App;

use Illuminate\Database\Eloquent\SoftDeletes;
use App\Extensions\ServissoModel;

/*
* Con esta clase se obtienen todas las validaciones que tiene una sucursal.
* Eg: Address(Recibo Agua o Luz), Teléfono o Alguna identificación (Licencia de conducir).
* Entre más validaciones tenga una sucursal, mejor.
*/
class BranchVerification extends ServissoModel
{
    //
	use SoftDeletes;
	
	protected $table = 'branch_verification';
	
	protected $fillable = array('branch_id', 'verification_type', 'description','url_verification_img');
	
	protected $hidden = ['deleted_at','created_at','updated_at','url_verification_img'];
	
	protected $searchFields = [
        'verification_type',
        'description'
    ];

    protected $betweenFields = [
        'created',
        'updated',
		'deleted',
    ];

    protected $orderByFields = [
        'created',
        'updated',
		'deleted',
		'verification_type',
		'description'
    ];
	
	public function branch()
    {
        // 1 branch is related to one company
        return $this->belongsTo('App\Branch');
    }
	
	public static function getRules(){
		$rules = [
			//'branch_id' => ['required','exists:branches,id'],
			'verification_type' => ['required','min:2','max:50']
			//'description' => ['required','min:2','max:150']		
		];

		return $rules;
	}

	/**
	* Se obtienen los mensajes de errores
	*/
	public static function getMessages(){
		$messages =
		[
			/*'branch_id.exists' => 'La Sucursal es obligatoria',
			'branch_id.required' => 'La Sucursal es obligatoria',*/
			'verification_type.min' => 'El tipo de verificación debe tener minimo :min caracteres',
			'verification_type.max' => 'El tipo de verificación debe tener máximo :max caracteres',
			'verification_type.required' => 'El tipo de verificación es obligatorio'
		/*	'description.min' => 'El tipo de descripción debe tener minimo :min caracteres',
			'description.max' => 'El tipo de descripción debe tener máximo :max caracteres',
			'description.required' => 'El tipo de descripción es obligatorio',*/
		];

		return $messages;
	}
}

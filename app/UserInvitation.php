<?php

namespace App;

use App\Extensions\ServissoModel;
use Illuminate\Database\Eloquent\SoftDeletes;

class UserInvitation extends ServissoModel
{
    //
	use SoftDeletes;
	
	protected $table = 'users_invitations';
	
	protected $fillable = array('user_id','to_user_id','invititation_type');
	
	protected $hidden = ['deleted_at','updated_at'];
	
	public function user()
    {
        // 1 branch is related to one company
        return $this->belongsTo('App\User');
    }
	
	public static function getRules(){
		$rules = [
			'user_id' => ['required','exists:users,id'],
			//'code' => ['required'],
			'invititation_type' => ['required']
			
		];

		return $rules;
	}

	/**
	* Se obtienen los mensajes de errores
	*/
	public static function getMessages(){
		$messages =
		[
			'user_id.required' => 'Usuario es obligatorio',
			'user_id.exists' => 'Usuario es obligatorio',
		//	'code.required' => 'Código es obligatorio',
			'invititation_type.required' => 'El tipo de invitación es obligatorio',
		];

		return $messages;
	}
}

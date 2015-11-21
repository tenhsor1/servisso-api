<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
class NewComment extends Model
{
    use SoftDeletes;
	protected $table="news_comments";
	

    protected $fillable = array('news_id','user_type','user_id','comment');


    protected $hidden = ['created_at','role_id','role','updated_at','deleted_at'];

    public function news()
    {
        // 1 comment is related to one new
        return $this->belongsTo('App\News');
    }
	
	  public static function getMessages(){
        $messages = [
            'required' => ':attribute is required',
            'email' => ':attribute has invalid format',
            'mimes' => ':attribute invalid format, allow: jpeg,png,bmp',
            'digits' => ':attribute should be 10 digits',
            'max' => ':attribute length too long',
            'min' => ':attribute length too short',
            'string' => ':attribute should be characters only'
        ];

        return $messages;
    }

    /**
     * Se obtienen las validaciones del modelo Partner
     */
     public static function getValidations(){
        $validation = [
			'news_id' => 'required',
            'user_id' => '',
            'comment' => 'required|max:500|min:7',
            'user_type' => '',
            'role_id' => '',
            'role' => ''
			];


        return $validation;
    }
}

<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
class News extends Model 
{
    use SoftDeletes;
	protected $table="news";
	

    protected $fillable = array('admin_id','title','content','image');


    protected $hidden = ['status','created_at','role_id','role','updated_at','deleted_at'];

    public function admin()
    {
        // 1 new is related to one admin who created it
        return $this->belongsTo('App\Admin');
    }

    public function comments()
    {
        // 1 new can have multiple comments
        return $this->hasMany('App\NewComment');
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
            'admin_id' => '',
            'title' => 'required|max:45|min:5',
            'content' => 'required|max:500|min:7',
            'image' => 'max:45|min:4',
            'status' => '',
            'role_id' => '',
            'role' => ''
			];


        return $validation;
    }
}

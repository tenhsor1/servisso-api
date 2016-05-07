<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class UserSocial extends Model
{
    use SoftDeletes;

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'user_socials';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [   'platform_id'
                            , 'platform'
                            , 'email'
                            , 'name'
                            , 'avatar'
                            , 'user_id'
                            , 'token'
                        ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = ['deleted_at', 'created_at', 'updated_at'];

    public function user()
    {
        // 1 admin can have one country
        return $this->hasOne('App\User');
    }

    public static function getMessages(){
        $messages =
        [
            'email.required' => 'Email es obligatorio',
            'email.email' => 'Email no válido',
            'name.required' => 'Nombre es obligatorio',
            'name.min' => 'Nombre debe tener minimo :min caracteres',
            'name.max' => 'Nombre debe tener máximo :max caracteres',
            'platform.required' => 'La plataforma social es obligatoria',
            'platform.in' => 'La plataforma social no es correcta',
            'token.required' => 'El token es obligatorio',
        ];

        return $messages;
    }

    public static function getRules(){
        $rules = array(
                'email' => ['required','email'],
                'name' => ['required','min:2','max:45'],
                'platform_id' => ['required'],
                'platform' => ['required','in:facebook'],
                'token' => ['required'],
            );

        return $rules;
    }

    public function scopeByUserIdandPlatform($query, $request){
        $userId = $request['user_id'];
        $platform = $request['platform'];
        if($userId && $platform){
            $query->where('user_id', '=', $userId);
            $query->where('platform', '=', $platform);
        }
        return $query;
    }
}

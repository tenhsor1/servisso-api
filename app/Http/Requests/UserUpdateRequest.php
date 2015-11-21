<?php

namespace App\Http\Requests;

use App\Http\Requests\Request;

class UserUpdateRequest extends Request
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'name' => 'max:45|min:2|alpha',
            'email' => 'unique:users|email',
            'password' => 'min:8',
            'last_name' => 'min:2|max:45|alpha',
            'phone' => 'max:20',
            'addess' => 'max:90',
            'zipcode' => 'max:10|alpha_num',
        ];
    }
}

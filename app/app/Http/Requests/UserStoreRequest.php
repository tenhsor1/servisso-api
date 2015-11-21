<?php

namespace App\Http\Requests;

use App\Http\Requests\Request;

class UserStoreRequest extends Request
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
            'name' => 'required|max:45|min:2|alpha',
            'email' => 'required|unique:users|email',
            'password' => 'required|min:8',
            'last_name' => 'required|min:2|max:45|alpha',
            'phone' => 'max:20',
            'address' => 'max:90',
            'zipcode' => 'max:10|alpha_num',
        ];
    }
}

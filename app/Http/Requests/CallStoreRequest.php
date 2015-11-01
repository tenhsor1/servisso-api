<?php

namespace App\Http\Requests;

use App\Http\Requests\Request;

class CallStoreRequest extends Request
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
            'status' => 'required|string|max:20',
            'to' => 'required|string|max:20',
            'from' => 'required|string|max:20',
            'answered' => 'required|string|max:3',
            'service_id' => 'required|integer|exists:services,id',

        ];
    }
}

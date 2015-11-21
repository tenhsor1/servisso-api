<?php

namespace App\Http\Requests;

use App\Http\Requests\Request;

class SmsStoreRequest extends Request
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
            'message' => 'required|string',
            'to' => 'required|string|max:20',
            'service_id' => 'required|integer|exists:services,id',

        ];
    }
}

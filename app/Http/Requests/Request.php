<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\JsonResponse;

abstract class Request extends FormRequest
{

    public function response(array $errors)
    {
        $errorJSON = ["error"=> "E_VALIDATION"
                        , "code"=> 422
                        , "data"=> $errors];
        return new JsonResponse($errorJSON, 422);
    }
}

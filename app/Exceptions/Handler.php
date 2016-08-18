<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Log;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that should not be reported.
     *
     * @var array
     */
    protected $dontReport = [
        HttpException::class,
        ModelNotFoundException::class,
    ];

    /**
     * Report or log an exception.
     *
     * This is a great spot to send exceptions to Sentry, Bugsnag, etc.
     *
     * @param  \Exception  $e
     * @return void
     */
    public function report(Exception $e)
    {
        return parent::report($e);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Exception  $e
     * @return \Illuminate\Http\Response
     */
    public function render($request, Exception $e)
    {
        if ($e instanceof ModelNotFoundException) {
            $e = new NotFoundHttpException($e->getMessage(), $e);
        }

        if ($e instanceof NotFoundHttpException){
            $message = $e->getMessage() ? $e->getMessage() : 'Endpoint not found';
            $code = $e->getStatusCode() ? $e->getStatusCode() : 404;

            $errorJSON = ['error' => $message, 'code' => $code];
            return response()->json($errorJSON, $code);
        }

        if ($e instanceof HttpException){
            $statusCode = $e->getStatusCode();

            if($statusCode == 400){
                $message = $e->getMessage() ? json_decode($e->getMessage()) : 'Bad Request';

                $errorJSON = ['error' => $message, 'message' => 'Bad Request', 'code' => $statusCode];
                return response()->json($errorJSON, $statusCode);
            }

            if($statusCode == 422){
                $message = $e->getMessage() ? json_decode($e->getMessage()) : 'Unprocessable Entity';

                $errorJSON = ['error' => 'Bad Request', 'data' => $message, 'code' => $statusCode];
                return response()->json($errorJSON, $statusCode);
            }


            $message = $e->getMessage() ? $e->getMessage() : 'Internal Server Error';
            $code = $e->getStatusCode() ? $e->getStatusCode() : 500;

            $errorJSON = ['error' => $message, 'code' => $code];
            return response()->json($errorJSON, $code);
        }

        return parent::render($request, $e);
    }
}

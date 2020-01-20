<?php

namespace OlaHub\Exceptions;

use Exception;
use Illuminate\Validation\ValidationException;
use Illuminate\Auth\Access\AuthorizationException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Laravel\Lumen\Exceptions\Handler as ExceptionHandler;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Log;

class Handler extends ExceptionHandler {

    /**
     * A list of the exception types that should not be reported.
     *
     * @var array
     */
    protected $dontReport = [
        AuthorizationException::class,
        HttpException::class,
        ModelNotFoundException::class,
        ValidationException::class,
    ];

    /**
     * Report or log an exception.
     *
     * This is a great spot to send exceptions to Sentry, Bugsnag, etc.
     *
     * @param  \Exception  $e
     * @return void
     */
    public function report(Exception $e) {
        parent::report($e);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Exception  $e
     * @return \Illuminate\Http\Response
     */
    public function render($request, Exception $e) {
        
        if ($e instanceof ModelNotFoundException) {
            Log::info('error: No data found');
            return response()->json((['status' => false, 'message' => 'No data found']), 200);
        }
        
        if ($e instanceof AuthorizationException) {
            Log::info('error: Insufficient privileges to perform this action');
            return response()->json((['status' => false, 'message' => 'Insufficient privileges to perform this action']), 200);
        }

        if ($e instanceof MethodNotAllowedHttpException) {
            Log::info('error: Method Not Allowed');
            return response()->json((['status' => false, 'message' => 'Method Not Allowed']), 200);
        }

        if ($e instanceof NotFoundHttpException) {
            Log::info('error: The requested resource was not found');
            return response()->json((['status' => false, 'message' => 'The requested resource was not found']), 200);
        }

        return parent::render($request, $e);
    }

}

<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\ValidationException;
use Laravel\Lumen\Exceptions\Handler as ExceptionHandler;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;

class Handler extends ExceptionHandler
{
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

    private array $exceptionsClassMap = [
        ModelNotFoundException::class => [
            'code' => 404,
            'message' => 'Resource not found',
        ],

        NotFoundHttpException::class => [
            'code' => 404,
            'message' => 'Invalid route',
        ],

        MethodNotAllowedHttpException::class => [
            'code' => 405,
            'message' => 'This method is not allowed for this endpoint.',
        ],
    ];

    /**
     * Report or log an exception.
     *
     * This is a great spot to send exceptions to Sentry, Bugsnag, etc.
     *
     * @param  Throwable  $exception
     * @return void
     *
     * @throws Exception
     */
    public function report(Throwable $exception)
    {
        parent::report($exception);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  Request  $request
     * @param  Throwable  $exception
     * @return Response|JsonResponse
     *
     * @throws Throwable
     */
    public function render($request, Throwable $exception)
    {
        if (!empty($response = $this->formatException($exception))) {
            if (env('app_env') === 'production') {
                return \response()->json(['error' => $response['message']], $response['code']);
            } else {
                return \response()->json(
                    ['error' => $response['message'], 'stack' => $response['stack']],
                    $response['code']);
            }
        }

        return parent::render($request, $exception);
    }

    private function formatException(Throwable $exception): ?array
    {
        $class = get_class($exception);

        if (array_key_exists($class, $this->exceptionsClassMap)) {
            return array_merge(
                    $this->exceptionsClassMap[$class],
                    [
                        'stack' => $exception->getTrace(),
                    ]) ?? null;
        }

        return null;
    }
}

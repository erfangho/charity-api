<?php

namespace App\Exceptions;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Exception\MethodNotAllowedException;
use Throwable;
use Exception;

class Handler extends ExceptionHandler
{
    /**
     * The list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            //
        });
    }

    protected function unauthenticated($request, AuthenticationException $exception): JsonResponse|Response|RedirectResponse
    {
        if ($request->expectsJson()) {
            return new JsonResponse(['error' => 'Unauthenticated.'], 401);
        }

        return redirect()->guest(route('login'));
    }

    public function render($request, Exception|Throwable $e)
    {
        if ($e instanceof MethodNotAllowedException) {
            $message = $e->getMessage();

            return new JsonResponse(['errors' => $message], 404);
        }

        if ($e instanceof ValidationException) {
            $errors = $e->errors();

            return new JsonResponse(['errors' => $errors], 422);
        }

        if ($e instanceof ModelNotFoundException) {

            return new JsonResponse(['errors' => $e->getMessage()], 404);
        }

        if ($e instanceof QueryException) {

            return new JsonResponse(['errors' => $e->getMessage()], 500);
        }

        return parent::render($request, $e);
    }
}

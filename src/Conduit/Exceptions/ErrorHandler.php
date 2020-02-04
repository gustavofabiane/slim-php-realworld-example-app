<?php

namespace Conduit\Exceptions;

use Throwable;
use Psr\Http\Message\ResponseInterface;
use Slim\Exception\HttpNotFoundException;
use Slim\Handlers\ErrorHandler as Handler;
use Psr\Http\Message\ServerRequestInterface;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class ErrorHandler extends Handler
{
    /** @inheritdoc */
    public function __invoke(
        ServerRequestInterface $request,
        Throwable $exception,
        bool $displayErrorDetails,
        bool $logErrors,
        bool $logErrorDetails
    ): ResponseInterface {
        if ($exception instanceof ModelNotFoundException) {
            $exception = new HttpNotFoundException($request, $exception->getMessage(), $exception);
        }

        return parent::__invoke(
            $request, 
            $exception,
            $displayErrorDetails,
            $logErrors,
            $logErrorDetails
        );
    }
}
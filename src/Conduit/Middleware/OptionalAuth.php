<?php

namespace Conduit\Middleware;

use Slim\DeferredCallable;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Tuupola\Middleware\JwtAuthentication;

class OptionalAuth implements MiddlewareInterface
{

    /**
     * @var ContainerInterface
     */
    private $jwtMiddleware;

    /**
     * OptionalAuth constructor
     *
     * @param JwtAuthentication $jwtMiddleware
     */
    public function __construct(JwtAuthentication $jwtMiddleware)
    {
        $this->jwtMiddleware = $jwtMiddleware;
    }

    /**
     * OptionalAuth middleware invokable class to verify JWT token when present in Request
     *
     * @param ServerRequestInterface $request
     * @param RequestHandlerInterface $handler
     * @return ResponseInterface
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        if ($request->hasHeader('HTTP_AUTHORIZATION') || $request->hasHeader('Authorization')) {
            return call_user_func([$this->jwtMiddleware, 'process'], $request, $handler);
        }
        
        return $handler->handle($request);
    }
}

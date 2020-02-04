<?php

namespace Conduit\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class Cors implements MiddlewareInterface
{
    /**
     * @var array|string
     */
    private $allowedOrigins = '*';

    /**
     * @var array|string
     */
    private $allowedHeaders = 'X-Requested-With, Content-Type, Accept, Origin, Authorization';

    /**
     * @var array|string
     */
    private $allowedMethods = 'GET, POST, PUT, DELETE, OPTIONS';

    /**
     * Cors Constructor
     *
     * @param array|string $settings
     */
    public function __construct($allowedOrigins)
    {
        $this->allowedOrigins = $allowedOrigins;
    }
    
    /**
     * Add Access Control headers in response
     *
     * @param ServerRequestInterface $request
     * @param RequestHandlerInterface $handler
     * @return ResponseInterface
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $response = $handler->handle($request);
    
        return $response
            ->withHeader('Access-Control-Allow-Origin', $this->allowedOrigins)
            ->withHeader('Access-Control-Allow-Headers', $this->allowedHeaders)
            ->withHeader('Access-Control-Allow-Methods', $this->allowedMethods);
    }
}












        
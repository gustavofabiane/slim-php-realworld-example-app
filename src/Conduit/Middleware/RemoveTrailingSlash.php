<?php

namespace Conduit\Middleware;

use Psr\Http\Message\UriInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Http\Message\ResponseFactoryInterface;

class RemoveTrailingSlash implements MiddlewareInterface
{
    /**
     * Response factory
     *
     * @var ResponseFactoryInterface
     */
    private $responseFactory;

    /**
     * RemoveTrailingSlash constructor
     *
     * @param ResponseFactoryInterface $responseFactory
     */
    public function __construct(ResponseFactoryInterface $responseFactory)
    {
        $this->responseFactory = $responseFactory;
    }

    /**
     * Remove the trailing slash from the request URI path
     *
     * @param ServerRequestInterface $request
     * @param RequestHandlerInterface $handler
     * @return ResponseInterface
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $uri = $request->getUri();
        $path = $uri->getPath();
        if ($path != '/' && substr($path, -1) == '/') {
            // permanently redirect paths with a trailing slash
            // to their non-trailing counterpart
            $uri = $uri->withPath(substr($path, 0, -1));
    
            if($request->getMethod() == 'GET') {
                return $this->respondWithRedirect($uri);
            }

            return $handler->handle($request->withUri($uri));
        }
    
        return $handler->handle($request);
    }

    /**
     * Respond with permanent redirect to given URI
     *
     * @param UriInterface $uri
     * @return ResponseInterface
     */
    private function respondWithRedirect(UriInterface $uri): ResponseInterface
    {
        return $this->responseFactory
            ->createResponse(301)
            ->withHeader('Location', (string) $uri);
    }
}

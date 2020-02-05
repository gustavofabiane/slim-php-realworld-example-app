<?php

namespace Conduit\Controllers;

use Conduit\Services\Auth\Auth;
use Conduit\Validation\Validator;
use Psr\Http\Message\UriInterface;
use Psr\Http\Message\ResponseInterface;
use League\Fractal\Manager as FractalManager;
use Psr\Http\Message\ResponseFactoryInterface;
use Illuminate\Database\Capsule\Manager as Capsule;

class BaseController
{
    /** @var ResponseFactoryInterface */
    protected $responseFactory;

    /** @var Validator */
    protected $validator;

    /** @var Capsule */
    protected $db;

    /** @var FractalManager */
    protected $fractal;

    /** @var Auth */
    protected $auth;

    /**
     * BaseController constructor
     *
     * @param ResponseFactoryInterface $responseFactory
     * @param Auth $auth
     * @param FractalManager $fractal
     * @param Validator $validator
     * @param Capsule $db
     */
    public function __construct(
        ResponseFactoryInterface $responseFactory,
        Auth $auth,
        FractalManager $fractal,
        Validator $validator,
        Capsule $db
    ) {
        $this->responseFactory = $responseFactory;
        $this->auth = $auth;
        $this->fractal = $fractal;
        $this->validator = $validator;
        $this->db = $db;
    }

    /**
     * JSON Response
     *
     * @param array $data
     * @param int $statusCode
     * @return ResponseInterface
     */
    protected function jsonResponse(array $data, int $statusCode = 200): ResponseInterface
    {
        $response = $this->response($statusCode)->withHeader('Content-Type', 'application/json');
        $response->getBody()->write(json_encode($data));
        $response->getBody()->rewind();

        return $response;
    }

    /**
     * Return a response with redirect header
     *
     * @param string|UriInterface $uri
     * @param int $statusCode
     * @return ResponseInterface
     */
    protected function redirect($uri, int $statusCode = 302): ResponseInterface
    {
        return $this->response($statusCode)->withHeader('Location', (string) $uri);
    }

    /**
     * Create response
     *
     * @param int $statusCode
     * @return ResponseInterface
     */
    private function response(int $statusCode):  ResponseInterface
    {
        return $this->responseFactory->createResponse($statusCode);
    }
}

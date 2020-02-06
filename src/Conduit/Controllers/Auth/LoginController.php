<?php

namespace Conduit\Controllers\Auth;

use Conduit\Controllers\BaseController;
use Conduit\Models\User;
use Conduit\Transformers\UserTransformer;
use League\Fractal\Resource\Item;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Respect\Validation\Validator as v;

class LoginController extends BaseController
{
    /**
     * Return token after successful login
     *
     * @param Request  $request
     *
     * @return Response
     */
    public function login(Request $request)
    {
        $validation = $this->validateLoginRequest($userParams = $request->getParsedBody()['user'] ?? null);

        if ($validation->failed()) {
            return $this->jsonResponse(['errors' => ['email or password' => ['is invalid']]], 422);
        }

        if ($user = $this->auth->attempt($userParams['email'], $userParams['password'])) {
            $user->token = $this->auth->generateToken($user);
            $data = $this->fractal->createData(new Item($user, new UserTransformer()))->toArray();

            return $this->jsonResponse(['user' => $data]);
        };

        return $this->jsonResponse(['errors' => ['email or password' => ['is invalid']]], 422);
    }

    /**
     * @param array
     *
     * @return \Conduit\Validation\Validator
     */
    protected function validateLoginRequest($values)
    {
        return $this->validator->validateArray($values,
            [
                'email'    => v::noWhitespace()->notEmpty(),
                'password' => v::noWhitespace()->notEmpty(),
            ]);
    }
}
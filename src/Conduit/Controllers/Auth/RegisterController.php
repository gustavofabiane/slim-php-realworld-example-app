<?php

namespace Conduit\Controllers\Auth;

use Conduit\Controllers\BaseController;
use Conduit\Models\User;
use Conduit\Transformers\UserTransformer;
use League\Fractal\Resource\Item;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Respect\Validation\Validator as v;

class RegisterController extends BaseController
{
    /**
     * Register New Users from POST Requests to /api/users
     *
     * @param Request  $request
     *
     * @return Response
     */
    public function register(Request $request): Response
    {
        $validation = $this->validateRegisterRequest(
            $userParams = $request->getParsedBody()['user'] ?? null
        );

        if ($validation->failed()) {
            return $this->jsonResponse(['errors' => $validation->getErrors()], 422);
        }

        $user = new User($userParams);
        $user->token = $this->auth->generateToken($user);
        $user->password = password_hash($userParams['password'], PASSWORD_DEFAULT);
        $user->save();

        $resource = new Item($user, new UserTransformer());
        $user = $this->fractal->createData($resource)->toArray();

        return $this->jsonResponse(['user' => $user]);
    }

    /**
     * @param array
     *
     * @return \Conduit\Validation\Validator
     */
    protected function validateRegisterRequest($values)
    {
        return $this->validator->validateArray($values, [
            'email'    => v::noWhitespace()->notEmpty()->email()->existsInTable($this->db->table('users'), 'email'),
            'username' => v::noWhitespace()->notEmpty()->existsInTable($this->db->table('users'), 'username'),
            'password' => v::noWhitespace()->notEmpty(),
        ]);
    }
}

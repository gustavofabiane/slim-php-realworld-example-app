<?php

namespace Conduit\Controllers\User;

use Conduit\Controllers\BaseController;
use Conduit\Transformers\UserTransformer;
use League\Fractal\Resource\Item;
use Psr\Http\Message\ServerRequestInterface as Request;
use Respect\Validation\Validator as v;

class UserController extends BaseController
{
    public function show(Request $request)
    {
        if ($user = $this->auth->requestUser($request)) {
            $data = $this->fractal->createData(new Item($user, new UserTransformer()))->toArray();

            return $this->jsonResponse(['user' => $data]);
        };
    }

    public function update(Request $request)
    {
        if ($user = $this->auth->requestUser($request)) {
            $requestParams = $request->getParsedBody()['user'] ?? null;

            $validation = $this->validateUpdateRequest($requestParams, $user->id);

            if ($validation->failed()) {
                return $this->jsonResponse(['errors' => $validation->getErrors()], 422);
            }

            $user->update([
                'email'    => isset($requestParams['email']) ? $requestParams['email'] : $user->email,
                'username' => isset($requestParams['username']) ? $requestParams['username'] : $user->username,
                'bio'      => isset($requestParams['bio']) ? $requestParams['bio'] : $user->bio,
                'image'    => isset($requestParams['image']) ? $requestParams['image'] : $user->image,
                'password' => isset($requestParams['password']) ? password_hash($requestParams['password'],
                    PASSWORD_DEFAULT) : $user->password,
            ]);

            $data = $this->fractal->createData(new Item($user, new UserTransformer()))->toArray();

            return $this->jsonResponse(['user' => $data]);
        };
    }

    /**
     * @param array
     *
     * @return \Conduit\Validation\Validator
     */
    protected function validateUpdateRequest($values, $userId)
    {
        return $this->validator->validateArray($values,
            [
                'email'    => v::optional(
                    v::noWhitespace()
                        ->notEmpty()
                        ->email()
                        ->existsWhenUpdate($this->db->table('users'), 'email', $userId)
                ),
                'username' => v::optional(
                    v::noWhitespace()
                        ->notEmpty()
                        ->existsWhenUpdate($this->db->table('users'), 'username', $userId)
                ),
                'password' => v::optional(v::noWhitespace()->notEmpty()),
            ]);
    }
}
<?php

namespace Conduit\Controllers\User;

use Conduit\Controllers\BaseController;
use Conduit\Models\User;
use Conduit\Transformers\ProfileTransformer;
use Conduit\Transformers\UserTransformer;
use League\Fractal\Resource\Item;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class ProfileController extends BaseController
{
    public function show(Request $request, Response $response, array $args)
    {
        $user = User::where('username', $args['username'])->firstOrFail();
        $requestUser = $this->auth->requestUser($request);
        $followingStatus = false;

        if ($requestUser) {
            $followingStatus = $requestUser->isFollowing($user->id);
        }

        return $this->jsonResponse(
            [
                'profile' => [
                    'username'  => $user->username,
                    'bio'       => $user->bio,
                    'image'     => $user->image,
                    'following' => $followingStatus,
                ],
            ]
        );
    }

    public function follow(Request $request, Response $response, array $args)
    {
        $requestUser = $this->auth->requestUser($request);
        $user = User::query()->where('username', $args['username'])->firstOrFail();

        $requestUser->follow($user->id);

        return $this->jsonResponse(
            [
                'profile' => [
                    'username'  => $user->username,
                    'bio'       => $user->bio,
                    'image'     => $user->image,
                    'following' => $user->isFollowedBy($requestUser),
                ],
            ]
        );
    }

    public function unfollow(Request $request, Response $response, array $args)
    {
        $requestUser = $this->auth->requestUser($request);
        $user = User::query()->where('username', $args['username'])->firstOrFail();

        $requestUser->unFollow($user->id);

        return $this->jsonResponse(
            [
                'profile' => [
                    'username'  => $user->username,
                    'bio'       => $user->bio,
                    'image'     => $user->image,
                    'following' => $requestUser->isFollowing($user->id),
                ],
            ]
        );
    }

}
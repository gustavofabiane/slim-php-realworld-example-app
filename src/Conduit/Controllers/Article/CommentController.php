<?php

namespace Conduit\Controllers\Article;

use Conduit\Controllers\BaseController;
use Conduit\Models\Article;
use Conduit\Models\Comment;
use Conduit\Transformers\CommentTransformer;
use League\Fractal\Resource\Collection;
use League\Fractal\Resource\Item;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Respect\Validation\Validator as v;

class CommentController extends BaseController
{
    /**
     * Return a all Comment for an article
     *
     * @param Request  $request
     * @param Response $response
     * @param array    $args
     *
     * @return Response
     */
    public function index(Request $request, Response $response, array $args)
    {
        $requestUserId = optional($this->auth->requestUser($request))->id;

        $article = Article::query()->with('comments')->where('slug', $args['slug'])->firstOrFail();

        $data = $this->fractal->createData(new Collection($article->comments,
            new CommentTransformer($requestUserId)))->toArray();

        return $this->jsonResponse(['comments' => $data['data']]);
    }

    /**
     * Create a new comment
     *
     * @param Request  $request
     * @param Response $response
     * @param array    $args
     *
     * @return Response
     */
    public function store(Request $request, Response $response, array $args)
    {
        $article = Article::query()->where('slug', $args['slug'])->firstOrFail();
        $requestUser = $this->auth->requestUser($request);

        if (is_null($requestUser)) {
            return $this->jsonResponse([], 401);
        }

        $this->validator->validateArray($data = $request->getParsedBody()['comment'],
            [
                'body' => v::notEmpty(),
            ]);

        if ($this->validator->failed()) {
            return $this->jsonResponse(['errors' => $this->validator->getErrors()], 422);
        }

        $comment = Comment::create([
            'body'       => $data['body'],
            'user_id'    => $requestUser->id,
            'article_id' => $article->id,
        ]);

        $data = $this->fractal->createData(new Item($comment, new CommentTransformer()))->toArray();

        return $this->jsonResponse(['comment' => $data]);

    }

    /**
     * Delete A Comment Endpoint
     *
     * @param Request  $request
     * @param Response $response
     * @param array    $args
     *
     * @return Response
     */
    public function destroy(Request $request, Response $response, array $args)
    {
        $comment = Comment::query()->findOrFail($args['id']);
        $requestUser = $this->auth->requestUser($request);

        if (is_null($requestUser)) {
            return $this->jsonResponse([], 401);
        }

        if ($requestUser->id != $comment->user_id) {
            return $this->jsonResponse(['message' => 'Forbidden'], 403);
        }

        $comment->delete();

        return $this->jsonResponse([], 200);
    }

}
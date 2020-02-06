<?php

namespace Conduit\Controllers\Article;

use Conduit\Controllers\BaseController;
use Conduit\Models\Article;
use Conduit\Transformers\ArticleTransformer;
use League\Fractal\Resource\Item;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class FavoriteController extends BaseController
{
    /**
     * Create a new article's favorite
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

        $requestUser->favoriteArticles()->syncWithoutDetaching($article->id);

        $data = $this->fractal->createData(new Item($article, new ArticleTransformer($requestUser->id)))->toArray();

        return $this->jsonResponse(['article' => $data]);

    }

    /**
     * Delete A Favorite
     *
     * @param Request  $request
     * @param Response $response
     * @param array    $args
     *
     * @return Response
     */
    public function destroy(Request $request, Response $response, array $args)
    {
        $article = Article::query()->where('slug', $args['slug'])->firstOrFail();
        $requestUser = $this->auth->requestUser($request);

        if (is_null($requestUser)) {
            return $this->jsonResponse([], 401);
        }

        $requestUser->favoriteArticles()->detach($article->id);

        $data = $this->fractal->createData(new Item($article, new ArticleTransformer($requestUser->id)))->toArray();

        return $this->jsonResponse(['article' => $data]);
    }

}
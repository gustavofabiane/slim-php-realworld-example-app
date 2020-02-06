<?php

namespace Conduit\Controllers\Article;

use Conduit\Controllers\BaseController;
use Conduit\Models\Article;
use Conduit\Models\Tag;
use Conduit\Transformers\ArticleTransformer;
use League\Fractal\Resource\Collection;
use League\Fractal\Resource\Item;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Respect\Validation\Validator as v;

class ArticleController extends BaseController
{
    /**
     * Return List of Articles
     *
     * @param Request  $request
     *
     * @return Response
     */
    public function index(Request $request): Response
    {
        // TODO Extract the logic of filtering articles to its own class

        $requestData = $request->getQueryParams();

        $requestUserId = optional($requestUser = $this->auth->requestUser($request))->id;
        $builder = Article::query()->latest()->with(['tags', 'user'])->limit(20);

        if ($request->getUri()->getPath() == '/api/articles/feed') {
            if (is_null($requestUser)) {
                return $this->jsonResponse([], 401);
            }
            $ids = $requestUser->followings->pluck('id');
            $builder->whereIn('user_id', $ids);
        }

        if ($author = $requestData['author'] ?? null) {
            $builder->whereHas('user', function ($query) use ($author) {
                $query->where('username', $author);
            });
        }

        if ($tag = $requestData['tag'] ?? null) {
            $builder->whereHas('tags', function ($query) use ($tag) {
                $query->where('title', $tag);
            });
        }

        if ($favoriteByUser = $requestData['favorited'] ?? null) {
            $builder->whereHas('favorites', function ($query) use ($favoriteByUser) {
                $query->where('username', $favoriteByUser);
            });
        }

        if ($limit = $requestData['limit'] ?? null) {
            $builder->limit($limit);
        }

        if ($offset = $requestData['offset'] ?? null) {
            $builder->offset($offset);
        }

        $articlesCount = $builder->count();
        $articles = $builder->get();

        $data = $this->fractal->createData(new Collection($articles,
            new ArticleTransformer($requestUserId)))->toArray();

        return $this->jsonResponse(['articles' => $data['data'], 'articlesCount' => $articlesCount]);
    }

    /**
     * Return a single Article to get article endpoint
     *
     * @param Request  $request
     * @param Response $response
     * @param array    $args
     *
     * @return Response
     */
    public function show(Request $request, Response $response, array $args)
    {
        $requestUserId = optional($this->auth->requestUser($request))->id;

        $article = Article::query()->where('slug', $args['slug'])->firstOrFail();

        $data = $this->fractal->createData(new Item($article, new ArticleTransformer($requestUserId)))->toArray();

        return $this->jsonResponse(['article' => $data]);
    }

    /**
     * Create and store a new Article
     *
     * @param Request  $request
     *
     * @return Response
     */
    public function store(Request $request)
    {
        $requestData = $request->getParsedBody();
        $requestUser = $this->auth->requestUser($request);

        if (is_null($requestUser)) {
            return $this->jsonResponse([], 401);
        }

        $this->validator->validateArray($data = $requestData['article'] ?? null,
            [
                'title'       => v::notEmpty(),
                'description' => v::notEmpty(),
                'body'        => v::notEmpty(),
            ]);

        if ($this->validator->failed()) {
            return $this->jsonResponse(['errors' => $this->validator->getErrors()], 422);
        }

        $article = new Article($requestData['article'] ?? null);
        $article->slug = str_slug($article->title);
        $article->user_id = $requestUser->id;
        $article->save();

        $tagsId = [];
        if (isset($data['tagList'])) {
            foreach ($data['tagList'] as $tag) {
                $tagsId[] = Tag::updateOrCreate(['title' => $tag], ['title' => $tag])->id;
            }
            $article->tags()->sync($tagsId);
        }

        $data = $this->fractal->createData(new Item($article, new ArticleTransformer()))->toArray();

        return $this->jsonResponse(['article' => $data]);

    }

    /**
     * Update Article Endpoint
     *
     * @param Request  $request
     * @param Response $response
     * @param array    $args
     *
     * @return Response
     */
    public function update(Request $request, Response $response, array $args)
    {
        $article = Article::query()->where('slug', $args['slug'])->firstOrFail();
        $requestUser = $this->auth->requestUser($request);

        if (is_null($requestUser)) {
            return $this->jsonResponse([], 401);
        }

        if ($requestUser->id != $article->user_id) {
            return $this->jsonResponse(['message' => 'Forbidden'], 403);
        }

        $params = $request->getParsedBody()['article'] ?? null;

        $article->update([
            'title'       => isset($params['title']) ? $params['title'] : $article->title,
            'description' => isset($params['description']) ? $params['description'] : $article->description,
            'body'        => isset($params['body']) ? $params['body'] : $article->body,
        ]);

        if (isset($params['title'])) {
            $article->slug = str_slug($params['title']);
        }

        $data = $this->fractal->createData(new Item($article, new ArticleTransformer()))->toArray();

        return $this->jsonResponse(['article' => $data]);
    }

    /**
     * Delete Article Endpoint
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

        if ($requestUser->id != $article->user_id) {
            return $this->jsonResponse(['message' => 'Forbidden'], 403);
        }

        $article->delete();

        return $this->jsonResponse([], 200);
    }

}
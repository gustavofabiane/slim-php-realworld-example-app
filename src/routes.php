<?php

use Slim\App;
use Conduit\Models\Tag;
use Conduit\Controllers\User\UserController;
use Conduit\Controllers\Auth\LoginController;
use Conduit\Controllers\User\ProfileController;
use Conduit\Controllers\Auth\RegisterController;
use Slim\Interfaces\RouteCollectorProxyInterface;
use Conduit\Controllers\Article\ArticleController;
use Conduit\Controllers\Article\CommentController;
use Conduit\Controllers\Article\FavoriteController;
use Psr\Http\Message\ResponseFactoryInterface;

return function (App $app): void {

    // Api Routes
    $app->group('/api', function (RouteCollectorProxyInterface $router) {

        $jwtMiddleware = $this->get('jwt');
        $optionalAuth = $this->get('optionalAuth');

        // Auth Routes
        $router->post('/users', RegisterController::class . ':register')->setName('auth.register');
        $router->post('/users/login', LoginController::class . ':login')->setName('auth.login');

        // User Routes
        $router->get('/user', UserController::class . ':show')->add($jwtMiddleware)->setName('user.show');
        $router->put('/user', UserController::class . ':update')->add($jwtMiddleware)->setName('user.update');

        // Profile Routes
        $router->get('/profiles/{username}', ProfileController::class . ':show')
            ->add($optionalAuth)
            ->setName('profile.show');
        $router->post('/profiles/{username}/follow', ProfileController::class . ':follow')
            ->add($jwtMiddleware)
            ->setName('profile.follow');
        $router->delete('/profiles/{username}/follow', ProfileController::class . ':unfollow')
            ->add($jwtMiddleware)
            ->setName('profile.unfollow');


        // Articles Routes
        $router->get('/articles/feed', ArticleController::class . ':index')->add($optionalAuth)->setName('article.index');
        $router->get('/articles/{slug}', ArticleController::class . ':show')->add($optionalAuth)->setName('article.show');
        $router->put('/articles/{slug}',
            ArticleController::class . ':update')->add($jwtMiddleware)->setName('article.update');
        $router->delete('/articles/{slug}',
            ArticleController::class . ':destroy')->add($jwtMiddleware)->setName('article.destroy');
        $router->post('/articles', ArticleController::class . ':store')->add($jwtMiddleware)->setName('article.store');
        $router->get('/articles', ArticleController::class . ':index')->add($optionalAuth)->setName('article.index');

        // Comments
        $router->get('/articles/{slug}/comments',
            CommentController::class . ':index')
            ->add($optionalAuth)
            ->setName('comment.index');
        $router->post('/articles/{slug}/comments',
            CommentController::class . ':store')
            ->add($jwtMiddleware)
            ->setName('comment.store');
        $router->delete('/articles/{slug}/comments/{id}',
            CommentController::class . ':destroy')
            ->add($jwtMiddleware)
            ->setName('comment.destroy');

        // Favorite Article Routes
        $router->post('/articles/{slug}/favorite',
            FavoriteController::class . ':store')
            ->add($jwtMiddleware)
            ->setName('favorite.store');
        $router->delete('/articles/{slug}/favorite',
            FavoriteController::class . ':destroy')
            ->add($jwtMiddleware)
            ->setName('favorite.destroy');

        // Tags Route
        $router->get('/tags', function () {
            $response = $this->get(ResponseFactoryInterface::class)->createResponse();
            $response->getBody()->write(json_encode([
                'tags' => Tag::all('title')->pluck('title'),
            ]));

            return $response->withHeader('Content-Type', 'application/json');
        });
    });

    // Home page
    $app->get('/', function () {
        $response = $this->get(ResponseFactoryInterface::class)->createResponse();
        $response->getBody()->write('Conduit - Slim Framework - Real World App');

        return $response;
    });
};

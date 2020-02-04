<?php

use Slim\App;
use Conduit\Middleware\Cors;
use Conduit\Exceptions\ErrorHandler;
use Conduit\Middleware\RemoveTrailingSlash;

/**
 * Application middleware
 */
return function (App $app): void {
    $settings = $app->getContainer()->get('settings');

    $errorMiddleware = $app->addErrorMiddleware(
        $settings['error']['display_error_details'],
        $settings['error']['log_errors'],
        $settings['error']['log_error_details'],
    );
    $errorMiddleware->setDefaultErrorHandler(new ErrorHandler(
        $app->getCallableResolver(),
        $app->getResponseFactory()
    ));

    $app->addBodyParsingMiddleware();
    $app->addRoutingMiddleware();

    $app->add(RemoveTrailingSlash::class);
    $app->add(Cors::class);
};

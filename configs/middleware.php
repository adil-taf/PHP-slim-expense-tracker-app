<?php

declare(strict_types=1);

use App\Config;
use App\Enum\AppEnvironment;
use App\Middleware\CsrfFieldsMiddleware;
use App\Middleware\ValidationExceptionMiddleware;
use App\Middleware\StartSessionsMiddleware;
use App\Middleware\ValidationErrorsMiddleware;
use Clockwork\Clockwork;
use Clockwork\Support\Slim\ClockworkMiddleware;
use App\Middleware\OldFormDataMiddleware;
use Slim\App;
use Slim\Middleware\MethodOverrideMiddleware;
use Slim\Views\Twig;
use Slim\Views\TwigMiddleware;

return function (App $app) {
    $container = $app->getContainer();
    $config    = $container->get(Config::class);

    $app->add(MethodOverrideMiddleware::class);
    $app->add(CsrfFieldsMiddleware::class);
    $app->add('csrf');
    $app->add(TwigMiddleware::create($app, $container->get(Twig::class)));
    $app->add(ValidationExceptionMiddleware::class);
    $app->add(OldFormDataMiddleware::class);
    $app->add(ValidationErrorsMiddleware::class);
    $app->add(StartSessionsMiddleware::class);
    if (AppEnvironment::isDevelopment($config->get('app_environment'))) {
        $app->add(new ClockworkMiddleware($app, $container->get(Clockwork::class)));
    }
    $app->addBodyParsingMiddleware();
    $app->addErrorMiddleware(
        (bool) $config->get('display_error_details'),
        (bool) $config->get('log_errors'),
        (bool) $config->get('log_error_details')
    );
};

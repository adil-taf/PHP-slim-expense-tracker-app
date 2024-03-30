<?php

declare(strict_types=1);

use App\Config;
use App\Enum\AppEnvironment;
use Psr\Container\ContainerInterface;
use Slim\Views\Twig;
use Twig\Extra\Intl\IntlExtension;

use function DI\create;

return [
    Config::class => create(Config::class)->constructor(require CONFIG_PATH . '/app.php'),

    Twig::class                   => function (Config $config, ContainerInterface $container) {
        $twig = Twig::create(VIEW_PATH, [
            'cache'       => STORAGE_PATH . '/cache/templates',
            'auto_reload' => AppEnvironment::isDevelopment($config->get('app_environment')),
        ]);

        $twig->addExtension(new IntlExtension());

        return $twig;
    },
];

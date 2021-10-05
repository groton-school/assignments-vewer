<?php

namespace GrotonSchool;

use Slim\App;
use DI\Container;
use GrotonSchool\Session\Middleware\SessionMiddleware;
use Psr\Container\ContainerInterface;
use Tuupola\Middleware\CorsMiddleware;

/** @var App $app */
/** @var Container $container */

$container->set(CorsMiddleware::class, function (ContainerInterface $container) {
    $settings = $container->get('settings')[CorsMiddleware::class];
    $corsOrigin = json_decode($settings['origin']);
    if (($i = array_search('@', $corsOrigin, true)) !== false) {
        // https://stackoverflow.com/a/64775366/294171
        // Actually reverse proxy on Heroku, so no HTTPS (because HTTP connection)
        $_SERVER['HTTPS'] = true; // FIXME probably a better way to do this... but who _isn't_ using https these days?
        $corsOrigin[$i] = ($_SERVER['HTTPS'] ? 'https' : 'http') . "://{$_SERVER['HTTP_HOST']}";
    }
    return new CorsMiddleware([
        'origin' => $corsOrigin,
        'headers.allow' => json_decode($settings['headers.allow']),
        'methods' => json_decode($settings['methods']),
        'cache' => $settings['cache'],
        'credentials' => true
    ]);
});

$container->set(SessionMiddleware::class, function () {
    return new SessionMiddleware();
});

// TODO#DEV adjust for production
$app->addErrorMiddleware(true, false, false);

$app->addBodyParsingMiddleware();

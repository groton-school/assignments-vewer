<?php

namespace GrotonSchool;

use DI\Container;
use GrotonSchool\BlackbaudSKY\Actions\AuthorizationCode;
use GrotonSchool\MajorAssignments\UserFactory;
use Slim\App;
use Slim\Http\Response;
use Slim\Http\ServerRequest;
use Slim\Interfaces\RouteCollectorProxyInterface as Collector;
use Tuupola\Middleware\CorsMiddleware;

/** @var App $app */
/** @var Container $container */

$userFactory = $container->get(UserFactory::class);

// LTI endpoints
$app->group('', function (Collector $lti) use ($app, $userFactory) {
    $lti->post('/launch', function (ServerRequest $request, Response $response) use ($app, $userFactory) {
        $user = $userFactory->getByUserId(
            $request->getParam('user_id'),
            $request->getParam('tool_consumer_instance_guid')
        );
        if (empty($user->refresh_token)) {
            session_start();
            $_SESSION['params'] = $request->getParams();
            return $response->withRedirect('/auth/token');
        }
        return $response->withJson($user);
    });
})->add(CorsMiddleware::class);

// OAuth client endpoints
$app->group('/auth', function (Collector $oauth2) {
    $oauth2->get('/token', AuthorizationCode::class);
    $oauth2->get('/redirect', AuthorizationCode::class);
});

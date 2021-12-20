<?php

use DI\Container;
use GrotonSchool\AssignmentsViewer\Users\User;
use GrotonSchool\AssignmentsViewer\Users\UserFactory;
use GrotonSchool\OAuth2\Client\Provider\BlackbaudSKY;
use GuzzleHttp\Client;

require_once __DIR__ . '/../bootstrap.php';
/** @var Container $container */

/** @var User */
$user = $container->get(UserFactory::class)-> getByInstance($_SESSION[CONSUMER_GUID], $_SESSION[USER_ID]);

/** @var BlackbaudSKY */
$sky = $container->get(BlackbaudSKY::class);

/** @var Client */
$client = $container->get(Client::class);

usleep(100000); // FIXME https://developer.blackbaud.com/skyapi/docs/in-depth-topics/api-request-throttling
$request = $sky->getAuthenticatedRequest(
    $_REQUEST['method'] ?: 'GET',
    preg_replace('/:user_id/', $user->getUserId(), $_REQUEST['url']), // TODO others?
    $sky->getAccessToken(),
    $_REQUEST['options'] ?: []
);

header('Content-Type: application/json');
echo $client->send($request)->getBody()->getContents();

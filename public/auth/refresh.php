<?php

use DI\Container;
use GrotonSchool\AssignmentsViewer\Users\UserFactory;
use GrotonSchool\OAuth2\Client\Provider\BlackbaudSKY;
use League\OAuth2\Client\Token\AccessToken;

require_once __DIR__ . '/../../bootstrap.php';
/** @var Container $container */

$sky = $container->get(BlackbaudSKY::class);
$user = $container->get(UserFactory::class)->getByInstance($_SESSION[CONSUMER_GUID], $_SESSION[USER_ID]);
/** @var AccessToken */
$accessToken = $sky->getAccessToken('refresh_token', ['refresh_token' => $user->refresh_token]);
$user->update([
    'refresh_token' => $accessToken->getRefreshToken(),
    'expires' => date('Y-m-d H:i:s', time() + $accessToken->getValues()['refresh_token_expires_in'])
]);
echo '<pre>';
var_dump($user);
var_dump($accessToken);

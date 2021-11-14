<?php

use DI\Container;
use GrotonSchool\AssignmentsViewer\UserFactory;
use GrotonSchool\OAuth2\Client\Provider\BlackbaudSKY;

require_once __DIR__ . '/../../bootstrap.php';
/** @var Container $container */

$sky = $container->get(BlackbaudSKY::class);
$user = $container->get(UserFactory::class)->getByUserId($_SESSION[USER_ID], $_SESSION[CONSUMER_GUID]);
storeToken($sky->getAccessToken('refresh_token', ['refresh_token' => $user->refresh_token]));

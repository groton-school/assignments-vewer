<?php

use DI\Container;
use GrotonSchool\OAuth2\Client\Provider\BlackbaudSKY;

require_once __DIR__ . '/../../bootstrap.php';
/** @var Container $container */

$sky = $container->get(BlackbaudSKY::class);

$sky->handleAuthorizationCodeFlow([
    $sky::OPT_ACCESS_TOKEN_CALLBACK => 'storeToken'
]);

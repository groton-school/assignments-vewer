<?php

use DI\Container;
use GrotonSchool\AssignmentsViewer\UserFactory;
use GrotonSchool\OAuth2\Client\Provider\BlackbaudSKY;
use League\OAuth2\Client\Token\AccessToken;

require_once __DIR__ . '/../../bootstrap.php';
/** @var Container $container */

$sky = new BlackbaudSKY([BlackbaudSKY::ACCESS_KEY => getenv('BLACKBAUD_SUBSCRIPTION_KEY')]);

$sky->handleAuthorizationCodeFlow([
    $this->sky::OPT_REDIRECT_URI => getenv('OAUTH_REDIRECT_URL'),
    $this->sky::OPT_ACCESS_TOKEN_CALLBACK => 'storeToken'
]);

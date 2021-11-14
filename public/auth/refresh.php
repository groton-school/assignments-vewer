<?php

use DI\Container;
use GrotonSchool\OAuth2\Client\Provider\BlackbaudSKY;

require_once __DIR__ . '/../../bootstrap.php';
/** @var Container $container */

$sky = new BlackbaudSKY([BlackbaudSKY::ACCESS_KEY => getenv('BLACKBAUD_SUBSCRIPTION_KEY')]);
storeToken($sky->getAccessToken('refreesh_token', [
    'client_id' => getenv('OAUTH_CLIENT_ID'),
    'client_secret' => getenv('OAUTH_CLIENT_SECRET')
]));

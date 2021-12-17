<?php

use ceLTIc\LTI\DataConnector\DataConnector;
use DI\Container;
use Dotenv\Dotenv;
use GrotonSchool\AssignmentsViewer\Users\UserFactory;
use GrotonSchool\OAuth2\Client\Provider\BlackbaudSKY;

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/constants.php';

if (file_exists(__DIR__ . '/.env')) {
    Dotenv::createImmutable(__DIR__)->load();
}
date_default_timezone_set(getenv('TZ'));

session_start();

$container = new Container();
$container->set(
    PDO::class,
    function () {
        $parts = parse_url(getenv('DATABASE_URL'));
        extract($parts);
        $path = ltrim($path, "/");
        return new PDO(
            "pgsql:host={$host};port={$port};dbname={$path};sslmode=require",
            $user,
            $pass
        );
    }
);
$container->set(
    DataConnector::class,
    function (Container $c) {
        return DataConnector::getDataConnector($c->get(PDO::class));
    }
);
$container->set(
    BlackbaudSKY::class,
    function () {
        return new BlackbaudSKY([
            'clientId' => getenv('OAUTH_CLIENT_ID'),
            'clientSecret' => getenv('OAUTH_CLIENT_SECRET'),
            'redirectUri' => getenv('OAUTH_REDIRECT_URL'),
            BlackbaudSKY::ACCESS_KEY => getenv('BLACKBAUD_SUBSCRIPTION_KEY')
        ]);
    }
);

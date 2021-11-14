<?php

use ceLTIc\LTI\DataConnector\DataConnector;
use DI\Container;
use Dotenv\Dotenv;
use League\OAuth2\Client\Token\AccessToken;
use PDO;

require __DIR__ . '/vendor/autoload.php';

Dotenv::createImmutable(__DIR__)->load();
date_default_timezone_set($_ENV['TIMEZONE']);

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
        return new DataConnector($c->get(PDO::class));
    }
);

function storeToken(AccessToken $accessToken)
{
    global $container;
    $expires = new DateTime('now');
    $expires->add(new DateInterval('PT' . $accessToken->getValues()['refresh_token_expires_in'] . 'S'));
    $container->get(UserFactory::class)->updateByUserId(
        $_SESSION[USER_ID],
        $_SESSION[CONSUMER_GUID],
        [
            'refresh_token' => $accessToken->getRefreshToken(),
            'expires'       => $expires->format(DateTime::ISO8601)
        ]
    );
    $_SESSION[TOKEN] = $accessToken->getToken();
    header('Location: ../app');
    exit;
}

define('USER_ID', 'user_id');
define('CONSUMER_GUID', 'tool_consumer_instance_guid');
define('IS_LEARNER', 'is_learner');
define('IS_STAFF', 'is_staff');
define('TOKEN', 'access_token');

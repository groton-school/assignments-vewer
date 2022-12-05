<?php

use Battis\LazySecrets\Secrets;
use ceLTIc\LTI\DataConnector\DataConnector;
use DI\Container;
use Dotenv\Dotenv;
use GrotonSchool\OAuth2\Client\Provider\BlackbaudSKY;
use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;
use GuzzleRetry\GuzzleRetryMiddleware;

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/constants.php';

if (file_exists(__DIR__ . '/.env')) {
    Dotenv::createImmutable(__DIR__)->load();
}
date_default_timezone_set(Secrets::get('TIMEZONE'));

session_start();

$container = new Container();
$container->set(
    PDO::class,
    function () {
        // FIXME move away from expensive Cloud SQL to BigTable!
        $socket = Secrets::get('DB_INSTANCE_SOCKET');
        $db = Secrets::get('DB_NAME');
        $user = Secrets::get('DB_USER');
        $password = Secrets::get('DB_PASSWORD');
        return new PDO(
            "pgsql:host={$socket};dbname={$db}",
            $user,
            $password
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
            'clientId' => Secrets::get('OAUTH_CLIENT_ID'),
            'clientSecret' => Secrets::get('OAUTH_CLIENT_SECRET'),
            'redirectUri' => Secrets::get('OAUTH_REDIRECT_URL'),
            BlackbaudSKY::ACCESS_KEY => Secrets::get('BLACKBAUD_SUBSCRIPTION_KEY'),
            BlackbaudSKY::ACCESS_TOKEN => $_SESSION[TOKEN] ?: null
        ]);
    }
);
$container->set(
    Client::class,
    function (BlackbaudSKY $sky) {
        $stack = HandlerStack::create();
        $stack->push(GuzzleRetryMiddleware::factory());
        return new Client([
            'base_uri' => $sky->getBaseApiUrl(),
            'handler' => $stack
        ]);
    }
);

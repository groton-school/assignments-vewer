<?php


namespace GrotonSchool;

use Psr\Container\ContainerInterface;
use DI\Container;
use GrotonSchool\OAuth2\Client\Provider\BlackbaudSKY;
use PDO;

/** @var Container $container */

$container->set(PDO::class, function (ContainerInterface $container) {
    $settings = $container->get('settings')[PDO::class];

    $pdo = new PDO($settings['dsn'], $settings['username'], $settings['password']);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

    if (strpos($settings['dsn'], 'sqlite') === 0) {
        $pdo->exec('PRAGMA foreign_keys = ON');
    }

    return $pdo;
});

$container->set(BlackbaudSKY::class, function (ContainerInterface $container) {
    return new BlackbaudSKY([BlackbaudSKY::ACCESS_KEY => getenv('BLACKBAUD_SUBSCRIPTION_KEY')]);
});

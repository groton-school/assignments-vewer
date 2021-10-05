<?php

namespace GrotonSchool;

use DI\Container;
use Dotenv\Dotenv;

require __DIR__ . '/../vendor/autoload.php';

if (file_exists(__DIR__ . '/../.env')) {
    Dotenv::createImmutable(__DIR__ . '/..')->load();
}
date_default_timezone_set($_ENV['TIMEZONE']);

$container = new Container();
$container->set('settings', require 'settings.php');

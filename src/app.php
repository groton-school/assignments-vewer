<?php

namespace GrotonSchool;

use DI\Container;
use Slim\Factory\AppFactory;

require __DIR__ . '/bootstrap.php';
/** @var Container $container */

$app = AppFactory::createFromContainer($container);

require __DIR__ . '/dependencies.php';
require __DIR__ . '/middleware.php';
require __DIR__ . '/routes.php';

$app->run();

<?php

use ceLTIc\LTI\DataConnector\DataConnector;
use ceLTIc\LTI\Platform;
use GrotonSchool\AssignmentsViewer\Google\Secrets;

require_once __DIR__ . '/../bootstrap.php';
/** @var DI\Container $container */

$dataConnector = DataConnector::getDataConnector($container->get(PDO::class));
$platform = Platform::fromConsumerKey(Secrets::get('LTI_CONSUMER_KEY'), $dataConnector);
$platform->name = 'Groton School';
$platform->secret = Secrets::get('LTI_CONSUMER_SECRET');
$platform->enabled = true;
$platform->save();

echo "{$platform->name} installed.";

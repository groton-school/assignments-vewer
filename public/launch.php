<?php

use DI\Container;
use GrotonSchool\AssignmentsViewer\AssignmentsViewerTool;

require_once __DIR__ . '/../bootstrap.php';
/** @var Container $container */

//$_SESSION = [];
//session_destroy();

/** @var AssignmentsViewerTool */
$tool = $container->get(AssignmentsViewerTool::class);
$tool->handleRequest();

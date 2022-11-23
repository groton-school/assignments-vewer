<?php

use DI\Container;
use GrotonSchool\AssignmentsViewer\AssignmentsViewerTool;

require_once __DIR__ . '/../bootstrap.php';
/** @var Container $container */

$_SESSION = [];
session_destroy();
session_start();

/** @var AssignmentsViewerTool */
$tool = $container->get(AssignmentsViewerTool::class);
$tool->handleRequest();

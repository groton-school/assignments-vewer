<?php

use DI\Container;
use GrotonSchool\AssignmentsViewer\AssignmentsViewerTool;

require_once __DIR__ . '/../../bootstrap.php';
/** @var Container $container */

/** @var AssignmentsViewerTool */
$lti = $container->get(AssignmentsViewerTool::class);
$lti->handleRequest();

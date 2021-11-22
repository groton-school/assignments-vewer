<?php

namespace GrotonSchool;

use DI\Container;
use PDO;

require __DIR__ . '/bootstrap.php';
/** @var Container $container */

/** @var PDO */
$pdo = $container->get(PDO::class);

$schemaPath = __DIR__ . '/schema';
if (file_exists($schemaPath) && is_dir($schemaPath)) {
    foreach (scandir($schemaPath) as $file) {
        if (!is_dir("$schemaPath/$file")) {
            if ($pdo->query(file_get_contents("$schemaPath/$file"))) {
                echo("`$file` loaded" . PHP_EOL);
            } else {
                echo("failed to load `$file`: " . $pdo->errorInfo() . PHP_EOL);
                break;
            }
        }
    }
} else {
    echo("`$schemaPath` is not a valid directory path" . PHP_EOL);
}

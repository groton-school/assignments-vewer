<?php

namespace GrotonSchool;

use DI\Container;
use Exception;
use PDO;

class Install
{
    public static function run($event, $schemaPath = __DIR__ . '/../schema')
    {
        try {
            require __DIR__ . '/bootstrap.php';
            require __DIR__ . '/settings.php';
            require __DIR__ . '/dependencies.php';
            /** @var Container $container */

            /** @var PDO */
            $pdo = $container->get(PDO::class);

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
        } catch (Exception $e) {
            echo(str_replace(dirname(dirname(dirname($e->getFile()))), '', $e->getFile()) . ':' . $e->getLine() . ': ' . ($e->getCode() ? '[' . $e->getCode() . '] ' : '') . $e->getMessage() . PHP_EOL);
        }
    }
}

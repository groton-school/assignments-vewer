<?php

$endpoint = @parse_url($_SERVER['REQUEST_URI'])['path'];
if (is_readable(__DIR__ . $endpoint)) {
    require(__DIR__ . $endpoint);
} elseif (is_readable(__DIR__ . $endpoint . '.php')) {
    require(__DIR__ . $endpoint . '.php');
} else {
    http_response_code(404);
    exit('Not Found');
}

<?php

namespace GrotonSchool;

use ceLTIc\LTI\Tool;
use League\OAuth2\Client\Provider\AbstractProvider as Provider;
use League\OAuth2\Client\Provider\GenericProvider;
use PDO;
use GrotonSchool\MajorAssignments\MajorAssignmentsTool;
use Tuupola\Middleware\CorsMiddleware;

return [
    PDO::class => (function () {
        $parts = parse_url(getenv('DATABASE_URL'));
        extract($parts);
        $path = ltrim($path, "/");
        return [
            'dsn' => "pgsql:host={$host};port={$port};dbname={$path};sslmode=require",
            "username" => $user,
            "password" => $pass
        ];
    })(),

    CorsMiddleware::class => [
        'origin' => $_ENV['CORS_ORIGIN'] ?: 'http' . ($_SERVER['HTTPS'] ? 's' : '') . "://{$_SERVER['HTTP_HOST']}",
        'headers.allow' => $_ENV['CORS_HEADERS'] ?: '["Authorization","Accept","Content-Type"]',
        'methods' => $_ENV['CORS_METHODS'] ?: '["POST","GET","OPTIONS"]',
        'cache' => $_ENV['CORS_CACHE'] ?: 0
    ],

    Tool::class => MajorAssignmentsTool::class,

    Provider::class => [
        'type' => GenericProvider::class,
        'params' => [
            'clientId' => getenv('OAUTH_CLIENT_ID'),
            'clientSecret' => getenv('OAUTH_CLIENT_SECRET'),
            'redirectUri' => getenv('OAUTH_REDIRECT_URL'),
            'urlAuthorize' => 'https://oauth2.sky.blackbaud.com/authorization',
            'urlAccessToken' => 'https://oauth2.sky.blackbaud.com/token',
            'urlResourceOwnerDetails' => ''
        ]
    ]
];

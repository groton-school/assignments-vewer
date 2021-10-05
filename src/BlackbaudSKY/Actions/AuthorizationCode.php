<?php

namespace GrotonSchool\BlackbaudSKY\Actions;

use DateInterval;
use DateTime;
use GrotonSchool\MajorAssignments\UserFactory;
use GrotonSchool\OAuth2\Client\Provider\BlackbaudSKY;
use Slim\Http\ServerRequest;
use Slim\Http\Response;

class AuthorizationCode
{
    private $sky;
    private $userFactory;

    public function __construct(BlackbaudSKY $sky, UserFactory $userFactory)
    {
        $this->sky = $sky;
        $this->userFactory = $userFactory;
    }

    public function __invoke(ServerRequest $request, Response $response)
    {
        return $this->sky->handleAuthorizationCodeFlow([
            $this->sky::OPT_PARAMS => $request->getParams(),
            $this->sky::OPT_REDIRECT_URI => getenv('OAUTH_REDIRECT_URL'),
            $this->sky::OPT_AUTH_CODE_CALLBACK => function () use ($response) {
                return $response->withHeader('Location', $this->sky->getAuthorizationUrl());
            },
            $this->sky::OPT_ACCESS_TOKEN_CALLBACK => function ($accessToken) use ($response) {
                $expires = new DateTime('now');
                $expires->add(new DateInterval('PT' . $accessToken->getValues()['refresh_token_expires_in'] . 'S'));
                $user = $this->userFactory->updateByUserId(
                    $_SESSION['params']['user_id'],
                    $_SESSION['params']['tool_consumer_instance_guid'],
                    [
                        'refresh_token' => $accessToken->getRefreshToken(),
                        'expires'       => $expires->format(DateTime::ISO8601)
                    ]
                );
                return $response->withJson($user);
            },
            $this->sky::OPT_ERROR_CALLBACK => function ($message, $code) use ($response) {
                $error = ['error' => $message];
                if (!empty($code)) {
                    $error['code'] = $code;
                }
                return $response->withJson($error);
            }
        ]);
    }
}

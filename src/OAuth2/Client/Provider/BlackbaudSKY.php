<?php

namespace GrotonSchool\OAuth2\Client\Provider;

use DateTime;
use Exception;
use League\OAuth2\Client\Provider\AbstractProvider;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use League\OAuth2\Client\Token\AccessToken;
use League\OAuth2\Client\Tool\ArrayAccessorTrait;
use Psr\Http\Message\ResponseInterface;

class BlackbaudSKY extends AbstractProvider
{
    const ACCESS_KEY = 'Bb-Api-Subscriber-Key';

    const SESSION_STATE = 'oauth2_state';

    const ARG_AUTH_CODE = 'authorization_code';

    const PARAM_CODE = 'code';
    const PARAM_STATE = 'state';

    const OPT_PARAMS = 'params';
    const OPT_REDIRECT_URI = 'redirect_uri';
    const OPT_AUTH_CODE_CALLBACK = 'authorization_code_callback';
    const OPT_ACCESS_TOKEN_CALLBACK = 'access_token_callback';
    const OPT_ERROR_CALLBACK = 'error_callback';

    use ArrayAccessorTrait;

    private $accessKey;

    public function __construct(array $options = [], array $collaborators = [])
    {
        parent::__construct($options, $collaborators);

        if (empty($options[self::ACCESS_KEY])) {
            throw new Exception('Blackbaud access key required');
        } else {
            $this->accessKey = $options[self::ACCESS_KEY];
        }
    }

    public function getBaseAuthorizationUrl()
    {
        return 'https://oauth2.sky.blackbaud.com/authorization';
    }

    public function getBaseAccessTokenUrl(array $params)
    {
        return 'https://oauth2.sky.blackbaud.com/token';
    }

    public function getResourceOwnerDetailsUrl(AccessToken $token)
    {
    }

    protected function getDefaultScopes()
    {
        return [];
    }

    protected function checkResponse(ResponseInterface $response, $data)
    {
    }

    protected function createResourceOwner(array $response, AccessToken $token)
    {
    }

    /**
     * Returns authorization headers for the 'bearer' grant.
     *
     * @param  AccessTokenInterface|string|null $token Either a string or an access token instance
     * @return array
     */
    protected function getAuthorizationHeaders($token = null)
    {
        return [
            self::ACCESS_KEY => $this->accessKey,
            'Authorization' => 'Bearer ' . $token
        ];
    }

    public function handleAuthorizationCodeFlow($options = [])
    {
        if (empty($options[self::OPT_REDIRECT_URI])) {
            $options[self::OPT_REDIRECT_URI] = $_SERVER['REQUEST_URI'];
        }

        if (empty($options[self::OPT_PARAMS])) {
            $options[self::OPT_PARAMS] = $_GET;
        }

        if (empty($options[self::OPT_AUTH_CODE_CALLBACK])) {
            $options[self::OPT_AUTH_CODE_CALLBACK] = function () {
                header('Location: ' . $this->getAuthorizationUrl());
                exit(0);
            };
        }

        if (empty($options[self::OPT_ACCESS_TOKEN_CALLBACK])) {
            $options[self::OPT_ACCESS_TOKEN_CALLBACK] = function ($accessToken) {
                echo json_encode($accessToken);
                exit(0);
            };
        }

        if (empty($options[self::OPT_ERROR_CALLBACK])) {
            $options[self::OPT_ERROR_CALLBACK] = function ($message, $code) {
                $error = ['error' => $message];
                if (!empty($code)) {
                    $error['code'] = $code;
                }
                echo json_encode($error);
                exit(0);
            };
        }

        session_start();
        if (empty($options[self::OPT_PARAMS][self::PARAM_CODE])) {
            call_user_func($options[self::OPT_AUTH_CODE_CALLBACK]);
        } elseif (empty($options[self::OPT_PARAMS][self::PARAM_STATE]) || (
            !empty($_SESSION[self::SESSION_STATE]) &&
            $options[self::OPT_PARAMS][self::PARAM_STATE] !== $_SESSION[self::SESSION_STATE]
            )) {
            if (!empty($_SESSION[self::SESSION_STATE])) {
                unset($_SESSION[self::SESSION_STATE]);
            }
            call_user_func($options[self::OPT_ERROR_CALLBACK], 'invalid state');
        } else {
            try {
                call_user_func($options[self::OPT_ACCESS_TOKEN_CALLBACK], $this->getAccessToken('authorization_code', ['code' => self::ARG_AUTH_CODE]));
            } catch (IdentityProviderException $e) {
                call_user_func($options[self::OPT_ERROR_CALLBACK], $e->getMessage(), $e->getCode());
            }
        }
    }
}

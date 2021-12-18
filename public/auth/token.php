<?php

use DI\Container;
use GrotonSchool\AssignmentsViewer\Users\UserFactory;
use GrotonSchool\OAuth2\Client\Provider\BlackbaudSKY;

require_once __DIR__ . '/../../bootstrap.php';
/** @var Container $container */

/** @var BlackbaudSKY */
$provider = $container->get(BlackbaudSKY::class);

// If we don't have an authorization code then get one
if (!isset($_GET['code'])) {
    // Fetch the authorization URL from the provider; this returns the
    // urlAuthorize option and generates and applies any necessary parameters
    // (e.g. state).
    $authorizationUrl = $provider->getAuthorizationUrl();

    // Get the state generated for you and store it to the session.
    $_SESSION['oauth2state'] = $provider->getState();

    // Redirect the user to the authorization URL.
    header('Location: ' . $authorizationUrl);
    exit;

// Check given state against previously stored one to mitigate CSRF attack
} elseif (empty($_GET['state']) || (isset($_SESSION['oauth2state']) && $_GET['state'] !== $_SESSION['oauth2state'])) {
    if (isset($_SESSION['oauth2state'])) {
        unset($_SESSION['oauth2state']);
    }

    exit('Invalid state');
} else {
    try {
        // Try to get an access token using the authorization code grant.
        $accessToken = $provider->getAccessToken('authorization_code', [
            'code' => $_GET['code']
        ]);

        $user = $container->get(UserFactory::class)->getByInstance($_SESSION[CONSUMER_GUID], $_SESSION[USER_ID]);
        $user->update([
            'refresh_token' => $accessToken->getRefreshToken(),
            'expires' => date('Y-m-d H:i:s', time() + $accessToken->getValues()['refresh_token_expires_in'])
        ]);
        $_SESSION[TOKEN] = $accessToken;
        header('Location: ../app.php');
        exit(0);
    } catch (\League\OAuth2\Client\Provider\Exception\IdentityProviderException $e) {
        echo $e->getMessage();
    }
}

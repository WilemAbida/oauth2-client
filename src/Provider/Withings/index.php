<?php

use waytohealth\OAuth2\Client\Provider\WithingsAccess;

$provider = new \Provider\WithingsAccess([
    'clientId'          => '{withings-oauth2-client-id}',
    'clientSecret'      => '{withings-client-secret}',
    'redirectUri'       => 'http://localhost:5000/user/signin/callback'
]);

// start the session
session_start();

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
} elseif (empty($_GET['state']) || array_key_exists('oauth2state', $_SESSION) && ($_GET['state'] !== $_SESSION['oauth2state'])) {
    unset($_SESSION['oauth2state']);
    exit('Invalid state');

} else {

    try {

        // Try to get an access token using the authorization code grant.
        $accessToken = $provider->getAccessToken('authorization_code', [
            'code' => $_GET['code']
        ]);

        // We have an access token, which we may use in authenticated
        // requests against the service provider's API.
        echo $accessToken->getToken() . "\n";
        echo $accessToken->getRefreshToken() . "\n";
        echo $accessToken->getExpires() . "\n";
        echo ($accessToken->hasExpired() ? 'expired' : 'not expired') . "\n";

        // Using the access token, we may look up details about the
        // resource owner.
        $resourceOwner = $provider->getResourceOwner($accessToken);

        var_export($resourceOwner->toArray());

        // The provider provides a way to get an authenticated API request for
        // the service, using the access token; it returns an object conforming
        // to Psr\Http\Message\RequestInterface.
        $request = $provider->getAuthenticatedRequest(
            \Provider\WithingsAccess::METHOD_GET,
            \Provider\WithingsAccess::BASE_WITHINGS_API_URL . '/v2/user?action=getdevice',
            $accessToken,
            ['headers' => [\Provider\WithingsAccess::HEADER_ACCEPT_LANG => 'en_US'], [\Provider\WithingsAccess::HEADER_ACCEPT_LOCALE => 'en_US']]
        // Fitbit uses the Accept-Language for setting the unit system used
        // and setting Accept-Locale will return a translated response if available.
        // https://dev.fitbit.com/docs/basics/#localization
        );
        // Make the authenticated API request and get the parsed response.
        $response = $provider->getParsedResponse($request);

        // If you would like to get the response headers in addition to the response body, use:
        //$response = $provider->getResponse($request);
        //$headers = $response->getHeaders();
        //$parsedResponse = $provider->parseResponse($response);

    } catch (\League\OAuth2\Client\Provider\Exception\IdentityProviderException $e) {

        // Failed to get the access token or user details.
        exit($e->getMessage());

    }

}


?>

<!DOCTYPE html>
<html lang="fr">
<body>

<?php
    echo '<p>Token accès:</p>';
    echo '<p><code>' .$accessToken. '</code></p>';

    if($accessToken != ""){
        echo '<p>Connecté</p>';
    }
    else {
        echo '<p><a href="https://account.withings.com/oauth2_user/authorize2/client_id=?68438600e7e6225de1a735879cdee096cbd9a1f32cd8ff75d28c6bfc537ffa7e"Connectez-vous avec Withings</a></p>';
    }
?>

</body>
</html>
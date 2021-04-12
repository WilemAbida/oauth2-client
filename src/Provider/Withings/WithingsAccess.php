<?php


namespace Provider;

use League\OAuth2\Client\Provider\AbstractProvider;
use League\OAuth2\Client\Provider\GenericResourceOwner;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use League\OAuth2\Client\Provider\ResourceOwnerInterface;
use League\OAuth2\Client\Token\AccessToken;
use League\OAuth2\Client\Tool\BearerAuthorizationTrait;
use Psr\Http\Message\ResponseInterface;


class WithingsAccess extends AbstractProvider
{


    /**
     * The basic Withings URL to access the website, as an authenticated user, using it provides us to use shorter links.
     *
     * @const string
     */
    const BASE_WITHINGS_URL = 'https://account.withings.com';

    /**
     * The URL of Withings to access to their API, using it provides us to use shorter links.
     *
     * @const string
     */
    const BASE_WITHINGS_API_URL = 'https://wbsapi.withings.net';

    /**
     * HTTP header Accept-Language.
     *
     * @const string
     */
    const HEADER_ACCEPT_LANG = 'Accept-Language';

    /**
     * HTTP header Accept-Locale.
     *
     * @const string
     */
    const HEADER_ACCEPT_LOCALE = 'Accept-Locale';

    /**
     * The id of owner's access token. Used to identify the resource owner.
     *
     * @var string
     */
    const ACCESS_TOKEN_RESOURCE_OWNER_ID = 'userid';

    /**
     * Get the authentication code, from Withings.
     *
     * @return string
     */
    public function getBaseAuthorizationUrl()
    {
        return static::BASE_WITHINGS_URL.'/oauth2_user/authorize2';
    }

    /**
     * Get the access token, from Withings.
     *
     * @param array $params
     *
     * @return string
     */
    public function getBaseAccessTokenUrl(array $params)
    {
        return static::BASE_WITHINGS_API_URL.'/v2/oauth2';
    }

    /**
     * Owner's profile/details.
     *
     * @param AccessToken $token
     *
     * @return string
     */
    public function getResourceOwnerDetailsUrl(AccessToken $token)
    {
        return static::BASE_WITHINGS_API_URL.'/v2/user?action=getdevice&access_token='.$token->getToken();
    }

    /**
     * Return data available from Withings.
     *
     * @return array
     */
    protected function getDefaultScopes()
    {
        return ['user.info','user.metrics','user.activity'];
    }

    /**
     * Return the authorization parameters.
     * We remove 'approval_prompt' because Withings doesn't use it.
     *
     * @param array $options
     *
     * @return array the authorization parameters
     */
    protected function getAuthorizationParameters(array $options)
    {
        $params = parent::getAuthorizationParameters($options);
        unset($params['approval_prompt']);
        if (!empty($options['prompt'])) {
            $params['prompt'] = $options['prompt'];
        }

        return $params;
    }

    /**
     * Checks Withings API response for errors.
     *
     * @param ResponseInterface $response
     * @param array|string $data
     * @throws IdentityProviderException
     *
     */
    protected function checkResponse(ResponseInterface $response, $data)
    {
        if (array_key_exists('error', $data)) {
            $errorMessage = $data['error'];
            $errorCode = array_key_exists('status', $data) ?
                $data['status'] : $response->getStatusCode();
            throw new IdentityProviderException(
                $errorMessage,
                $errorCode,
                $data
            );
        }
    }

    /**
     * Generate a resource owner from a successful resource owner details request.
     *
     * @param array $response
     * @param AccessToken $token
     * @return GenericResourceOwner
     *
     */
    protected function createResourceOwner(array $response, AccessToken $token)
    {
        return new GenericResourceOwner($response, self::ACCESS_TOKEN_RESOURCE_OWNER_ID);
    }

    /**
     * Revoke access for the given token.
     *
     * @param AccessToken $accessToken
     *
     * @return mixed
     */
    public function revoke(AccessToken $accessToken)
    {
        $options = $this->optionProvider->getAccessTokenOptions($this->getAccessTokenMethod(), []);
        $uri = $this->appendQuery(
            self::BASE_WITHINGS_API_URL.'/notify?action=revoke',
            $this->buildQueryString(['token' => $accessToken->getToken()])
        );
        $request = $this->getRequest(self::METHOD_POST, $uri, $options);

        return $this->getResponse($request);
    }

    public function parseResponse(ResponseInterface $response)
    {
        return parent::parseResponse($response);
    }

}
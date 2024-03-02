<?php
/**
 * Class DummyOAuth2Provider
 *
 * @created      16.03.2021
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2021 smiley
 * @license      MIT
 */
declare(strict_types=1);

namespace chillerlan\OAuthTest\Providers\OAuth2;

use chillerlan\OAuth\Core\{
	AccessToken, ClientCredentials, CSRFToken, OAuth2Provider, ProviderException, TokenInvalidate, TokenRefresh
};

/**
 * An OAuth2 provider implementation that supports token refresh, csrf tokens and client credentials
 */
final class DummyOAuth2Provider extends OAuth2Provider implements ClientCredentials, CSRFToken, TokenRefresh, TokenInvalidate{

	protected string  $authURL        = 'https://example.com/oauth2/authorize';
	protected string  $accessTokenURL = 'https://example.com/oauth2/token';
	protected string  $revokeURL      = 'https://example.com/oauth2/revoke';
	protected string  $apiURL         = 'https://api.example.com/';
	protected ?string $userRevokeURL  = 'https://account.example.com/apps/';
	protected array   $authHeaders    = ['foo' => 'bar'];
	protected array   $apiHeaders     = ['foo' => 'bar'];
	protected int     $authMethod     = self::AUTH_METHOD_QUERY;

	/**
	 * @inheritDoc
	 */
	public function invalidateAccessToken(AccessToken $token = null):bool{

		if($token === null && !$this->storage->hasAccessToken()){
			throw new ProviderException('no token given');
		}

		$response = $this->request($this->revokeURL);

		if($response->getStatusCode() === 200){
			$this->storage->clearAccessToken();

			return true;
		}

		return false;
	}

}

<?php
/**
 * Class DummyOAuth1Provider
 *
 * @created      16.03.2021
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2021 smiley
 * @license      MIT
 */
declare(strict_types=1);

namespace chillerlan\OAuthTest\Providers\OAuth1;

use chillerlan\OAuth\Core\{AccessToken, OAuth1Provider, ProviderException, TokenInvalidate};

/**
 * An OAuth1 provider implementation
 */
final class DummyOAuth1Provider extends OAuth1Provider implements TokenInvalidate{

	protected string  $authURL         = 'https://example.com/oauth/authorize';
	protected string  $accessTokenURL  = 'https://example.com/oauth/access_token';
	protected string  $requestTokenURL = 'https://example.com/oauth/request_token';
	protected string  $revokeURL       = 'https://example.com/oauth/revoke';
	protected string  $apiURL          = 'https://api.example.com';
	protected ?string $userRevokeURL   = 'https://account.example.com/apps/';
	protected array   $authHeaders     = ['foo' => 'bar'];
	protected array   $apiHeaders      = ['foo' => 'bar'];

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

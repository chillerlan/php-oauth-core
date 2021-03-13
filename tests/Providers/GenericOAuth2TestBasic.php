<?php
/**
 * Class GenericOAuth2TestBasic
 *
 * @created      06.08.2019
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2019 smiley
 * @license      MIT
 */

namespace chillerlan\OAuthTest\Providers;

use chillerlan\OAuth\Core\{OAuth2Provider, OAuthInterface, ProviderException};
use ReflectionClass;

/**
 * @property \chillerlan\OAuth\Core\OAuth2Interface $provider
 */
class GenericOAuth2TestBasic extends OAuth2ProviderTestAbstract{

	/**
	 * @return \chillerlan\OAuth\Core\OAuthInterface
	 */
	protected function getProvider():OAuthInterface{

		$provider = new class($this->initHttp(), $this->storage, $this->options, $this->logger) extends OAuth2Provider{

			protected string $authURL        = 'https://example.com/oauth2/authorize';
			protected string $accessTokenURL = 'https://example.com/oauth2/token';
			protected ?string $apiURL        = 'https://api.example.com/';
			protected ?string $userRevokeURL = 'https://account.example.com/apps/';
			protected ?string $endpointMap   = TestEndpoints::class;

		};

		$this->reflection = new ReflectionClass($provider);

		return $provider;
	}

	public function testRefreshAccessTokenException():void{
		$this->expectException(ProviderException::class);
		$this->expectExceptionMessage('token refresh not supported');

		$this->provider->refreshAccessToken();
	}

	public function testGetClientCredentialsTokenException():void{
		$this->expectException(ProviderException::class);
		$this->expectExceptionMessage('client credentials token not supported');

		$this->provider->getClientCredentialsToken();
	}

}

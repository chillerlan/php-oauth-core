<?php
/**
 * Class GenericOAuth2Test
 *
 * @created      09.09.2018
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2018 Smiley
 * @license      MIT
 */

namespace chillerlan\OAuthTest\Providers;

use chillerlan\OAuth\Core\{ClientCredentials, CSRFToken, OAuth2Provider, OAuthInterface, TokenRefresh};
use ReflectionClass;

/**
 * @property \chillerlan\OAuth\Core\OAuth2Interface $provider
 */
class GenericOAuth2Test extends OAuth2ProviderTestAbstract{

	/**
	 * @return \chillerlan\OAuth\Core\OAuthInterface
	 */
	protected function getProvider():OAuthInterface{

		$provider = new class($this->initHttp(), $this->storage, $this->options, $this->logger)
			extends OAuth2Provider implements ClientCredentials, CSRFToken, TokenRefresh{

			protected string $authURL        = 'https://example.com/oauth2/authorize';
			protected string $accessTokenURL = 'https://example.com/oauth2/token';
			protected ?string $apiURL        = 'https://api.example.com/';
			protected ?string $userRevokeURL = 'https://account.example.com/apps/';
			protected ?string $endpointMap   = TestEndpoints::class;
			protected array $authHeaders     = ['foo' => 'bar'];
			protected array $apiHeaders      = ['foo' => 'bar'];
			protected int $authMethod        = OAuth2Provider::AUTH_METHOD_QUERY;

		};

		$this->reflection = new ReflectionClass($provider);

		return $provider;
	}

}

<?php
/**
 * Class GenericOAuth1Test
 *
 * @filesource   GenericOAuth1Test.php
 * @created      09.09.2018
 * @package      chillerlan\OAuthTest\Core
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2018 Smiley
 * @license      MIT
 */

namespace chillerlan\OAuthTest\Providers;

use chillerlan\OAuth\Core\{OAuth1Provider, OAuthInterface};
use ReflectionClass;

/**
 * @property \chillerlan\OAuth\Core\OAuth1Interface $provider
 */
class GenericOAuth1Test extends OAuth1ProviderTestAbstract{

	/**
	 * @return \chillerlan\OAuth\Core\OAuthInterface
	 */
	protected function getProvider():OAuthInterface{

		$provider = new class($this->initHttp(), $this->storage, $this->options, $this->logger) extends OAuth1Provider{

			protected $apiURL          = 'https://api.example.com';
			protected $requestTokenURL = 'https://example.com/oauth/request_token';
			protected $authURL         = 'https://example.com/oauth/authorize';
			protected $accessTokenURL  = 'https://example.com/oauth/access_token';
			protected $userRevokeURL   = 'https://account.example.com/apps/';
			protected $endpointMap     = TestEndpoints::class;
			protected $authHeaders     = ['foo' => 'bar'];
			protected $apiHeaders      = ['foo' => 'bar'];

		};

		$this->reflection = new ReflectionClass($provider);

		return $provider;
	}

}

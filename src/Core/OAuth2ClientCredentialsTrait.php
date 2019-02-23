<?php
/**
 * Trait OAuth2ClientCredentialsTrait
 *
 * @filesource   OAuth2ClientCredentialsTrait.php
 * @created      29.01.2018
 * @package      chillerlan\OAuth\Core
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2018 smiley
 * @license      MIT
 */

namespace chillerlan\OAuth\Core;

/**
 * @implements \chillerlan\OAuth\Core\ClientCredentials
 *
 * @property array                                           $authHeaders
 * @property string                                          $scopesDelimiter
 * @property string                                          $clientCredentialsTokenURL
 * @property string                                          $accessTokenURL
 * @property \chillerlan\OAuth\Storage\OAuthStorageInterface $storage
 * @property \chillerlan\HTTP\Psr18\HTTPClientInterface      $http
 * @property \Psr\Http\Message\RequestFactoryInterface       $requestFactory
 * @property \Psr\Http\Message\StreamFactoryInterface        $streamFactory
 */
trait OAuth2ClientCredentialsTrait{

	/**
	 * @param array $scopes
	 *
	 * @return \chillerlan\OAuth\Core\AccessToken|\chillerlan\Settings\SettingsContainerInterface
	 */
	public function getClientCredentialsToken(array $scopes = null):AccessToken{
		$params = ['grant_type' => 'client_credentials'];

		if($scopes !== null){
			$params['scope'] = implode($this->scopesDelimiter, $scopes);
		}

		$request = $this->requestFactory
			->createRequest('POST', $this->clientCredentialsTokenURL ?? $this->accessTokenURL)
			->withHeader('Authorization', 'Basic '.base64_encode($this->options->key.':'.$this->options->secret))
			->withHeader('Content-Type', 'application/x-www-form-urlencoded')
			->withBody($this->streamFactory->createStream(http_build_query($params, '', '&', PHP_QUERY_RFC1738)))
		;

		foreach($this->authHeaders as $header => $value){
			$request = $request->withAddedHeader($header, $value);
		}

		$token = $this->parseTokenResponse($this->http->sendRequest($request));

		$this->storage->storeAccessToken($this->serviceName, $token);

		return $token;
	}

}

<?php
/**
 * Class OAuth2ProviderTestAbstract
 *
 * @created      09.09.2018
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2018 smiley
 * @license      MIT
 */
declare(strict_types=1);

namespace chillerlan\OAuthTest\Providers;

use chillerlan\HTTP\Utils\{MessageUtil, QueryUtil};
use chillerlan\OAuth\OAuthException;
use chillerlan\OAuth\Core\{AccessToken, ClientCredentials, CSRFToken, OAuth2Interface, TokenRefresh};
use chillerlan\OAuth\Providers\ProviderException;
use function explode, parse_url, sleep, time;
use const PHP_URL_QUERY;

/**
 * @property \chillerlan\OAuth\Core\OAuth2Interface $provider
 */
abstract class OAuth2ProviderTestAbstract extends OAuthProviderTestAbstract{

	protected const TEST_PROPERTIES = [
		'apiURL'                    => 'https://localhost/oauth2/api',
		'accessTokenURL'            => 'https://localhost/oauth2/access_token',
		'refreshTokenURL'           => 'https://localhost/oauth2/refresh_token',
		'clientCredentialsTokenURL' => 'https://localhost/oauth2/client_credentials',
		'revokeURL'                 => 'https://localhost/oauth2/revoke_token',
	];

	protected const TEST_RESPONSES = [
		'/oauth2/access_token'       =>
			'{"access_token":"test_access_token","expires_in":3600,"state":"test_state","scope":"some_scope other_scope"}',
		'/oauth2/refresh_token'      =>
			'{"access_token":"test_refreshed_access_token","expires_in":60,"state":"test_state"}',
		'/oauth2/revoke_token'       =>
			'{"message":"token revoked"}',
		'/oauth2/client_credentials' =>
			'{"access_token":"test_client_credentials_token","expires_in":30,"state":"test_state"}',
		'/oauth2/api/request'        =>
			'{"data":"such data! much wow!"}',
	];

	protected function setUp():void{
		parent::setUp();

		$this->provider->storeAccessToken(new AccessToken(['accessToken' => 'foo']));
		$this->storage->storeCSRFState('test_state', $this->provider->serviceName);
	}

	public function testOAuth2Instance():void{
		$this::assertInstanceOf(OAuth2Interface::class, $this->provider);
	}

	public function testGetAuthURL():void{
		$url   = $this->provider->getAuthURL(['client_secret' => 'foo'], ['some_scope']);
		$query = QueryUtil::parse(parse_url((string)$url, PHP_URL_QUERY));

		$this::assertArrayNotHasKey('client_secret', $query);
		$this::assertSame($this->options->key, $query['client_id']);
		$this::assertSame('code', $query['response_type']);
		$this::assertSame(explode('?', (string)$url)[0], $this->reflection->getProperty('authURL')->getValue($this->provider));
	}

	public function testGetAccessToken():void{
		$token = $this->provider->getAccessToken('foo', 'test_state');

		$this::assertSame('test_access_token', $token->accessToken);
		$this::assertSame(['some_scope', 'other_scope'], $token->scopes);
		$this::assertGreaterThan(time(), $token->expires);
	}

	public function testParseTokenResponseNoDataException():void{
		$this->expectException(ProviderException::class);
		$this->expectExceptionMessage('unable to parse token response');

		$this->invokeReflectionMethod(
			'parseTokenResponse',
			[$this->responseFactory->createResponse()->withBody($this->streamFactory->createStream('""'))],
		);
	}

	public function testParseTokenResponseErrorException():void{
		$this->expectException(ProviderException::class);
		$this->expectExceptionMessage('error retrieving access token');

		$this->invokeReflectionMethod(
			'parseTokenResponse',
			[$this->responseFactory->createResponse()->withBody($this->streamFactory->createStream('{"error":"whatever"}'))],
		);
	}

	public function testParseTokenResponseNoTokenException():void{
		$this->expectException(ProviderException::class);
		$this->expectExceptionMessage('token missing');

		$this->invokeReflectionMethod(
			'parseTokenResponse',
			[$this->responseFactory->createResponse()->withBody($this->streamFactory->createStream('{"foo":"bar"}'))],
		);
	}

	public function testGetRequestAuthorization():void{
		$request = $this->requestFactory->createRequest('GET', 'https://foo.bar');
		$token   = new AccessToken(['accessTokenSecret' => 'test_token_secret', 'accessToken' => 'test_token']);

		$authMethod = $this->reflection->getProperty('authMethod')->getValue($this->provider);

		// header (default)
		if($authMethod === OAuth2Interface::AUTH_METHOD_HEADER){
			$this::assertStringContainsString(
				$this->reflection->getProperty('authMethodHeader')->getValue($this->provider).' test_token',
				$this->provider->getRequestAuthorization($request, $token)->getHeaderLine('Authorization')
			);
		}
		// query
		elseif($authMethod === OAuth2Interface::AUTH_METHOD_QUERY){
			$this::assertStringContainsString(
				$this->reflection->getProperty('authMethodQuery')->getValue($this->provider).'=test_token',
				$this->provider->getRequestAuthorization($request, $token)->getUri()->getQuery()
			);
		}

	}

	public function testRequest():void{
		$token = new AccessToken(['accessToken' => 'test_access_token_secret', 'expires' => 1]);
		$this->provider->storeAccessToken($token);

		$this::assertSame('such data! much wow!', MessageUtil::decodeJSON($this->provider->request('/request'))->data);
	}

	public function testRequestInvalidAuthTypeException():void{
		$this->expectException(OAuthException::class);
		$this->expectExceptionMessage('invalid auth type');

		$this->reflection->getProperty('authMethod')->setValue($this->provider, -1);

		$token = new AccessToken(['accessToken' => 'test_access_token_secret', 'expires' => 1]);
		$this->provider->storeAccessToken($token);

		$this->provider->request('/request');
	}

	public function testCheckCSRFState():void{

		if(!$this->provider instanceof CSRFToken){
			$this->markTestSkipped('CSRFToken N/A');
		}

		// will throw an exception if it goes wrong
		$this->invokeReflectionMethod('checkState', ['test_state']);

		$this->expectNotToPerformAssertions();
	}

	public function testCheckStateInvalidException():void{

		if(!$this->provider instanceof CSRFToken){
			$this->markTestSkipped('CSRFToken N/A');
		}

		$this->expectException(ProviderException::class);
		$this->expectExceptionMessage('invalid state');

		$this->invokeReflectionMethod('checkState');
	}

	public function testCheckStateInvalidCSRFStateException():void{

		if(!$this->provider instanceof CSRFToken){
			$this->markTestSkipped('CSRFToken N/A');
		}

		$this->expectException(ProviderException::class);
		$this->expectExceptionMessage('invalid CSRF state');

		$this->invokeReflectionMethod('checkState', ['invalid_test_state']);
	}

	public function testRefreshAccessTokenNoRefreshTokenAvailable():void{

		if(!$this->provider instanceof TokenRefresh){
			$this->markTestSkipped('TokenRefresh N/A');
		}

		$this->expectException(OAuthException::class);
		$this->expectExceptionMessage('no refresh token available, token expired [');

		$token = new AccessToken(['expires' => 1, 'refreshToken' => null]);
		$this->provider->storeAccessToken($token);

		$this->provider->refreshAccessToken();
	}

	public function testRefreshAccessToken():void{

		if(!$this->provider instanceof TokenRefresh){
			$this->markTestSkipped('TokenRefresh N/A');
		}

		$token = new AccessToken(['expires' => 1, 'refreshToken' => 'test_refresh_token']);
		$this->provider->storeAccessToken($token);

		$token = $this->provider->refreshAccessToken();

		$this::assertSame('test_refresh_token', $token->refreshToken);
		$this::assertSame('test_refreshed_access_token', $token->accessToken);
		$this::assertGreaterThan(time(), $token->expires);
	}

	public function testRequestWithTokenRefresh():void{

		if(!$this->provider instanceof TokenRefresh){
			$this->markTestSkipped('TokenRefresh N/A');
		}

		$token = new AccessToken(['accessToken' => 'test_access_token', 'refreshToken' => 'test_refresh_token', 'expires' => 1]);
		$this->provider->storeAccessToken($token);

		sleep(2);

		$this::assertSame('such data! much wow!', MessageUtil::decodeJSON($this->provider->request('/request'))->data);
	}

	public function testGetClientCredentials():void{

		if(!$this->provider instanceof ClientCredentials){
			$this->markTestSkipped('ClientCredentials N/A');
		}

		$token = $this->provider->getClientCredentialsToken(['some_scope']);

		$this::assertSame('test_client_credentials_token', $token->accessToken);
		$this::assertGreaterThan(time(), $token->expires);
	}

}

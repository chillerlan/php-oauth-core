<?php
/**
 * Class OAuth2ProviderTestAbstract
 *
 * @created      09.09.2018
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2018 smiley
 * @license      MIT
 */

namespace chillerlan\OAuthTest\Providers;

use chillerlan\HTTP\Utils\QueryUtil;
use chillerlan\OAuth\Core\{AccessToken, ClientCredentials, CSRFToken, OAuth2Interface, ProviderException, TokenRefresh};
use chillerlan\OAuth\OAuthException;
use function explode;
use function parse_url;
use function sleep;
use function time;
use const PHP_URL_QUERY;

/**
 * @property \chillerlan\OAuth\Core\OAuth2Interface $provider
 */
abstract class OAuth2ProviderTestAbstract extends OAuthProviderTestAbstract{

	protected array $testResponses = [
		'/oauth2/access_token'       =>
			'{"access_token":"test_access_token","expires_in":3600,"state":"test_state"}',
		'/oauth2/refresh_token'      =>
			'{"access_token":"test_refreshed_access_token","expires_in":60,"state":"test_state"}',
		'/oauth2/client_credentials' =>
			'{"access_token":"test_client_credentials_token","expires_in":30,"state":"test_state"}',
		'/oauth2/api/request'        =>
			'{"data":"such data! much wow!"}',
	];

	protected function setUp():void{
		parent::setUp();

		$this->setProperty($this->provider, 'apiURL', 'https://localhost/oauth2/api');
		$this->setProperty($this->provider, 'accessTokenURL', 'https://localhost/oauth2/access_token');
		$this->setProperty($this->provider, 'refreshTokenURL', 'https://localhost/oauth2/refresh_token');
		$this->setProperty($this->provider, 'clientCredentialsTokenURL', 'https://localhost/oauth2/client_credentials');

		$this->storage->storeCSRFState('test_state', $this->provider->serviceName);
	}

	public function testOAuth2Instance():void{
		$this::assertInstanceOf(OAuth2Interface::class, $this->provider);
	}

	public function testGetAuthURL():void{
		$url   = $this->provider->getAuthURL(['client_secret' => 'foo'], ['some_scope']);
		$query = QueryUtil::parse(parse_url($url, PHP_URL_QUERY));

		$this::assertArrayNotHasKey('client_secret', $query);
		$this::assertSame($this->options->key, $query['client_id']);
		$this::assertSame('code', $query['response_type']);
		$this::assertSame(explode('?', $url)[0], $this->getProperty('authURL')->getValue($this->provider));
	}

	public function testGetAccessToken():void{
		$token = $this->provider->getAccessToken('foo', 'test_state');

		$this::assertSame('test_access_token', $token->accessToken);
		$this::assertGreaterThan(time(), $token->expires);
	}

	public function testParseTokenResponseNoDataException():void{
		$this->expectException(ProviderException::class);
		$this->expectExceptionMessage('unable to parse token response');

		$this
			->getMethod('parseTokenResponse')
			->invokeArgs($this->provider, [
				$this->responseFactory->createResponse()->withBody($this->streamFactory->createStream('""')),
			])
		;
	}

	public function testParseTokenResponseErrorException():void{
		$this->expectException(ProviderException::class);
		$this->expectExceptionMessage('error retrieving access token');

		$this
			->getMethod('parseTokenResponse')
			->invokeArgs($this->provider, [
				$this->responseFactory->createResponse()->withBody($this->streamFactory->createStream('{"error":"whatever"}')),
			])
		;
	}

	public function testParseTokenResponseNoTokenException():void{
		$this->expectException(ProviderException::class);
		$this->expectExceptionMessage('token missing');

		$this
			->getMethod('parseTokenResponse')
			->invokeArgs($this->provider, [
				$this->responseFactory->createResponse()->withBody($this->streamFactory->createStream('{"foo":"bar"}')),
			])
		;
	}

	public function testGetRequestAuthorization():void{
		$request = $this->requestFactory->createRequest('GET', 'https://foo.bar');
		$token   = new AccessToken(['accessTokenSecret' => 'test_token_secret', 'accessToken' => 'test_token']);

		$authMethod = $this->getProperty('authMethod')->getValue($this->provider);

		// header (default)
		if($authMethod === OAuth2Interface::AUTH_METHOD_HEADER){
			$this::assertStringContainsString(
				$this->getProperty('authMethodHeader')->getValue($this->provider).' test_token',
				$this->provider->getRequestAuthorization($request, $token)->getHeaderLine('Authorization')
			);
		}
		// query
		elseif($authMethod === OAuth2Interface::AUTH_METHOD_QUERY){
			$this::assertStringContainsString(
				$this->getProperty('authMethodQuery')->getValue($this->provider).'=test_token',
				$this->provider->getRequestAuthorization($request, $token)->getUri()->getQuery()
			);
		}

	}

	public function testRequest():void{
		$token = new AccessToken(['accessToken' => 'test_access_token_secret', 'expires' => 1]);
		$this->provider->storeAccessToken($token);

		$this::assertSame('such data! much wow!', $this->responseJson($this->provider->request('/request'))->data);
	}

	public function testRequestInvalidAuthTypeException():void{
		$this->expectException(OAuthException::class);
		$this->expectExceptionMessage('invalid auth type');

		$this->setProperty($this->provider, 'authMethod', -1);
		$token = new AccessToken(['accessToken' => 'test_access_token_secret', 'expires' => 1]);
		$this->provider->storeAccessToken($token);

		$this->provider->request('/request');
	}

	public function testCheckCSRFState():void{

		if(!$this->provider instanceof CSRFToken){
			$this->markTestSkipped('CSRFToken N/A');
		}

		// will throw an exception if it goes wrong
		$this
			->getMethod('checkState')
			->invokeArgs($this->provider, ['test_state']);

		$this->expectNotToPerformAssertions();
	}

	public function testCheckStateInvalidException():void{

		if(!$this->provider instanceof CSRFToken){
			$this->markTestSkipped('CSRFToken N/A');
		}

		$this->expectException(ProviderException::class);
		$this->expectExceptionMessage('invalid state');

		$this
			->getMethod('checkState')
			->invoke($this->provider);
	}

	public function testCheckStateInvalidCSRFStateException():void{

		if(!$this->provider instanceof CSRFToken){
			$this->markTestSkipped('CSRFToken N/A');
		}

		$this->expectException(ProviderException::class);
		$this->expectExceptionMessage('invalid CSRF state');

		$this
			->getMethod('checkState')
			->invokeArgs($this->provider, ['invalid_test_state']);
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

		$this::assertSame('such data! much wow!', $this->responseJson($this->provider->request('/request'))->data);
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

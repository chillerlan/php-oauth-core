<?php
/**
 * Class OAuth2ProviderTestAbstract
 *
 * @filesource   OAuth2ProviderTestAbstract.php
 * @created      09.09.2018
 * @package      chillerlan\OAuthTest\Providers
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2018 smiley
 * @license      MIT
 */

namespace chillerlan\OAuthTest\Providers;

use chillerlan\HTTP\Psr7\{Request, Response};
use chillerlan\HTTP\Psr17;
use chillerlan\OAuth\Core\{AccessToken, AccessTokenForRefresh, ClientCredentials, CSRFToken, OAuth2Interface, TokenRefresh};

/**
 * @property \chillerlan\OAuth\Core\OAuth2Interface $provider
 */
abstract class OAuth2ProviderTestAbstract extends ProviderTestAbstract{

	protected $responses = [
		'/oauth2/access_token' => [
			'access_token' => 'test_access_token',
			'expires_in'   => 3600,
			'state'        => 'test_state',
		],
		'/oauth2/refresh_token' =>  [
			'access_token' => 'test_refreshed_access_token',
			'expires_in'   => 60,
			'state'        => 'test_state',
		],
		'/oauth2/client_credentials' => [
			'access_token' => 'test_client_credentials_token',
			'expires_in'   => 30,
			'state'        => 'test_state',
		],
		'/oauth2/api/request' => [
			'data' => 'such data! much wow!'
		],
		'/oauth2/api/request/test/get' => ['foo'],
	];

	protected $authMethodHeader = OAuth2Interface::AUTH_METHODS_HEADER[OAuth2Interface::HEADER_BEARER];

	protected function setUp(){
		parent::setUp();

		$this->setProperty($this->provider, 'apiURL', 'https://localhost/oauth2/api/request');
		$this->setProperty($this->provider, 'accessTokenURL', 'https://localhost/oauth2/access_token');

		if($this->provider instanceof TokenRefresh){
			$this->setProperty($this->provider, 'refreshTokenURL', 'https://localhost/oauth2/refresh_token');
		}

		if($this->provider instanceof ClientCredentials){
			$this->setProperty($this->provider, 'clientCredentialsTokenURL', 'https://localhost/oauth2/client_credentials');
		}

		if($this->provider instanceof CSRFToken){
			$this->storage->storeCSRFState($this->provider->serviceName, 'test_state');
		}

	}

	public function testOAuth2Instance(){
		$this->assertInstanceOf(OAuth2Interface::class, $this->provider);
	}

	public function testGetAuthURL(){
		$url = $this->provider->getAuthURL(['client_secret' => 'foo'], ['some_scope']);
		parse_str(parse_url($url, PHP_URL_QUERY), $query);

		$this->assertArrayNotHasKey('client_secret', $query);
		$this->assertSame($this->options->key, $query['client_id']);
		$this->assertSame('code', $query['response_type']);
		$this->assertSame(explode('?', $url)[0], $this->getProperty('authURL')->getValue($this->provider));
	}

	public function testGetAccessToken(){
		$token = $this->provider->getAccessToken('foo', 'test_state');

		$this->assertSame('test_access_token', $token->accessToken);
		$this->assertGreaterThan(time(), $token->expires);
	}

	/**
	 * @expectedException \chillerlan\OAuth\Core\ProviderException
	 * @expectedExceptionMessage unable to parse token response
	 */
	public function testParseTokenResponseNoData(){
		$this
			->getMethod('parseTokenResponse')
			->invokeArgs($this->provider, [(new Response)->withBody(Psr17\create_stream_from_input('whatever'))])
		;
	}

	/**
	 * @expectedException \chillerlan\OAuth\Core\ProviderException
	 * @expectedExceptionMessage error retrieving access token
	 */
	public function testParseTokenResponseError(){
		$this
			->getMethod('parseTokenResponse')
			->invokeArgs($this->provider, [(new Response)->withBody(Psr17\create_stream_from_input('{"error":"whatever"}'))])
		;
	}

	/**
	 * @expectedException \chillerlan\OAuth\Core\ProviderException
	 * @expectedExceptionMessage token missing
	 */
	public function testParseTokenResponseNoToken(){
		$this
			->getMethod('parseTokenResponse')
			->invokeArgs($this->provider, [(new Response)->withBody(Psr17\create_stream_from_input('{"foo":"bar"}'))])
		;
	}

	public function testGetRequestAuthorization(){
		$request = new Request('GET', 'https://foo.bar');
		$token   = new AccessToken(['accessTokenSecret' => 'test_token_secret', 'accessToken' => 'test_token']);

		// header (default)
		$this->assertContains($this->authMethodHeader.'test_token', $this->provider->getRequestAuthorization($request, $token)->getHeaderLine('Authorization'));

		// query
		$this->setProperty($this->provider, 'authMethod', OAuth2Interface::QUERY_ACCESS_TOKEN);
		$this->assertContains('access_token=test_token', $this->provider->getRequestAuthorization($request, $token)->getUri()->getQuery());
	}

	public function testRequest(){
		$token = new AccessToken(['accessToken' => 'test_access_token_secret', 'expires' => 1]);
		$this->storage->storeAccessToken($this->provider->serviceName, $token);

		$this->assertSame('such data! much wow!', json_decode($this->provider->request('')->getBody()->getContents())->data);
	}

	/**
	 * @expectedException \chillerlan\OAuth\OAuthException
	 * @expectedExceptionMessage invalid auth type
	 */
	public function testRequestInvalidAuthType(){
		$this->setProperty($this->provider, 'authMethod', 'foo');
		$token = new AccessToken(['accessToken' => 'test_access_token_secret', 'expires' => 1]);
		$this->storage->storeAccessToken($this->provider->serviceName, $token);

		$this->provider->request('');
	}

	public function testCheckCSRFState(){

		if(!$this->provider instanceof CSRFToken){
			$this->markTestSkipped('CSRFToken N/A');
			return;
		}

		$provider = $this
			->getMethod('checkState')
			->invokeArgs($this->provider, ['test_state']);

		$this->assertInstanceOf(OAuth2Interface::class, $provider);
	}

	/**
	 * @expectedException \chillerlan\OAuth\Core\ProviderException
	 * @expectedExceptionMessage invalid state
	 */
	public function testCheckStateInvalid(){

		if(!$this->provider instanceof CSRFToken){
			$this->markTestSkipped('CSRFToken N/A');
			return;
		}

		$this
			->getMethod('checkState')
			->invoke($this->provider);
	}

	/**
	 * @expectedException \chillerlan\OAuth\Core\ProviderException
	 * @expectedExceptionMessage invalid CSRF state
	 */
	public function testCheckStateInvalidCSRFState(){

		if(!$this->provider instanceof CSRFToken){
			$this->markTestSkipped('CSRFToken N/A');
			return;
		}

		$this
			->getMethod('checkState')
			->invokeArgs($this->provider, ['invalid_test_state']);
	}

	/**
	 * @expectedException \chillerlan\OAuth\OAuthException
	 * @expectedExceptionMessage no refresh token available, token expired [
	 */
	public function testRefreshAccessTokenNoRefreshTokenAvailable(){

		if(!$this->provider instanceof TokenRefresh){
			$this->markTestSkipped('TokenRefresh N/A');
			return;
		}

		if($this->provider instanceof AccessTokenForRefresh){
			$this->markTestSkipped('N/A, AccessTokenForRefresh');
			return;
		}

		$token = new AccessToken(['expires' => 1, 'refreshToken' => null]);
		$this->storage->storeAccessToken($this->provider->serviceName, $token);

		$this->provider->refreshAccessToken();
	}

	public function testRefreshAccessToken(){

		if(!$this->provider instanceof TokenRefresh){
			$this->markTestSkipped('TokenRefresh N/A');
			return;
		}

		$token = new AccessToken(['expires' => 1, 'refreshToken' => 'test_refresh_token']);
		$this->storage->storeAccessToken($this->provider->serviceName, $token);

		$token = $this->provider->refreshAccessToken();

		$this->assertSame('test_refresh_token', $token->refreshToken);
		$this->assertSame('test_refreshed_access_token', $token->accessToken);
		$this->assertGreaterThan(time(), $token->expires);
	}

	public function testRequestWithTokenRefresh(){

		if(!$this->provider instanceof TokenRefresh){
			$this->markTestSkipped('TokenRefresh N/A');
			return;
		}

		$token = new AccessToken(['accessToken' => 'test_access_token', 'refreshToken' => 'test_refresh_token', 'expires' => 1]);
		$this->storage->storeAccessToken($this->provider->serviceName, $token);

		sleep(2);

		$this->assertSame('such data! much wow!', json_decode($this->provider->request('')->getBody()->getContents())->data);
	}

	public function testRequestWithTokenRefreshAccessTokenForRefresh(){

		if(!$this->provider instanceof AccessTokenForRefresh){
			$this->markTestSkipped('AccessTokenForRefresh N/A');
			return;
		}

		$token = new AccessToken(['accessToken' => 'test_access_token', 'expires' => 1]);
		$this->storage->storeAccessToken($this->provider->serviceName, $token);

		sleep(2);

		$this->assertSame('such data! much wow!', json_decode($this->provider->request('')->getBody()->getContents())->data);
	}

	public function testGetClientCredentials(){

		if(!$this->provider instanceof ClientCredentials){
			$this->markTestSkipped('ClientCredentials N/A');
			return;
		}

		$token = $this->provider->getClientCredentialsToken(['some_scope']);

		$this->assertSame('test_client_credentials_token', $token->accessToken);
		$this->assertGreaterThan(time(), $token->expires);
	}

}

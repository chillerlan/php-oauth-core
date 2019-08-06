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

use chillerlan\HTTP\{Psr17, Psr7};
use chillerlan\HTTP\Psr7\{Request, Response};
use chillerlan\OAuth\Core\{AccessToken, ClientCredentials, CSRFToken, OAuth2Interface, ProviderException, TokenRefresh};
use chillerlan\OAuth\OAuthException;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\{RequestInterface, ResponseInterface};

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

	protected function setUp():void{
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

	/**
	 * @return \Psr\Http\Client\ClientInterface
	 */
	protected function initHttp():ClientInterface{
		return new class($this->responses, $this->logger) extends ProviderTestHttpClient{

			public function sendRequest(RequestInterface $request):ResponseInterface{
				$stream = Psr17\create_stream_from_input(json_encode($this->responses[$request->getUri()->getPath()]));

				return $this->logRequest($request, (new Response)->withBody($stream));
			}

		};
	}

	public function testOAuth2Instance(){
		$this->assertInstanceOf(OAuth2Interface::class, $this->provider);
	}

	public function testGetAuthURL(){
		$url = $this->provider->getAuthURL(['client_secret' => 'foo'], ['some_scope']);
		\parse_str(\parse_url($url, \PHP_URL_QUERY), $query);

		$this->assertArrayNotHasKey('client_secret', $query);
		$this->assertSame($this->options->key, $query['client_id']);
		$this->assertSame('code', $query['response_type']);
		$this->assertSame(\explode('?', $url)[0], $this->getProperty('authURL')->getValue($this->provider));
	}

	public function testGetAccessToken(){
		$token = $this->provider->getAccessToken('foo', 'test_state');

		$this->assertSame('test_access_token', $token->accessToken);
		$this->assertGreaterThan(\time(), $token->expires);
	}

	public function testParseTokenResponseNoDataException(){
		$this->expectException(ProviderException::class);
		$this->expectExceptionMessage('unable to parse token response');

		$this
			->getMethod('parseTokenResponse')
			->invokeArgs($this->provider, [(new Response)->withBody(Psr17\create_stream_from_input(''))])
		;
	}

	public function testParseTokenResponseErrorException(){
		$this->expectException(ProviderException::class);
		$this->expectExceptionMessage('error retrieving access token');

		$this
			->getMethod('parseTokenResponse')
			->invokeArgs($this->provider, [(new Response)->withBody(Psr17\create_stream_from_input('{"error":"whatever"}'))])
		;
	}

	public function testParseTokenResponseNoTokenException(){
		$this->expectException(ProviderException::class);
		$this->expectExceptionMessage('token missing');

		$this
			->getMethod('parseTokenResponse')
			->invokeArgs($this->provider, [(new Response)->withBody(Psr17\create_stream_from_input('{"foo":"bar"}'))])
		;
	}

	public function testGetRequestAuthorization(){
		$request = new Request('GET', 'https://foo.bar');
		$token   = new AccessToken(['accessTokenSecret' => 'test_token_secret', 'accessToken' => 'test_token']);

		$authMethod = $this->getProperty('authMethod')->getValue($this->provider);

		// header (default)
		if(isset(OAuth2Interface::AUTH_METHODS_HEADER[$authMethod])){
			$this->assertStringContainsString(OAuth2Interface::AUTH_METHODS_HEADER[$authMethod].'test_token', $this->provider->getRequestAuthorization($request, $token)->getHeaderLine('Authorization'));
		}
		// query
		elseif(isset(OAuth2Interface::AUTH_METHODS_QUERY[$authMethod])){
			$this->assertStringContainsString(OAuth2Interface::AUTH_METHODS_QUERY[$authMethod].'=test_token', $this->provider->getRequestAuthorization($request, $token)->getUri()->getQuery());
		}

	}

	public function testRequest(){
		$token = new AccessToken(['accessToken' => 'test_access_token_secret', 'expires' => 1]);
		$this->storage->storeAccessToken($this->provider->serviceName, $token);

		$this->assertSame('such data! much wow!', Psr7\get_json($this->provider->request(''))->data);
	}

	public function testRequestInvalidAuthTypeException(){
		$this->expectException(OAuthException::class);
		$this->expectExceptionMessage('invalid auth type');

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

		// will throw an exception if it goes wrong
		$this
			->getMethod('checkState')
			->invokeArgs($this->provider, ['test_state']);

		$this->expectNotToPerformAssertions();
	}

	public function testCheckStateInvalidException(){
		$this->expectException(ProviderException::class);
		$this->expectExceptionMessage('invalid state');

		if(!$this->provider instanceof CSRFToken){
			$this->markTestSkipped('CSRFToken N/A');
			return;
		}

		$this
			->getMethod('checkState')
			->invoke($this->provider);
	}

	public function testCheckStateInvalidCSRFStateException(){
		$this->expectException(ProviderException::class);
		$this->expectExceptionMessage('invalid CSRF state');

		if(!$this->provider instanceof CSRFToken){
			$this->markTestSkipped('CSRFToken N/A');
			return;
		}

		$this
			->getMethod('checkState')
			->invokeArgs($this->provider, ['invalid_test_state']);
	}

	public function testRefreshAccessTokenNoRefreshTokenAvailable(){
		$this->expectException(OAuthException::class);
		$this->expectExceptionMessage('no refresh token available, token expired [');

		if(!$this->provider instanceof TokenRefresh){
			$this->markTestSkipped('TokenRefresh N/A');
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
		$this->assertGreaterThan(\time(), $token->expires);
	}

	public function testRequestWithTokenRefresh(){

		if(!$this->provider instanceof TokenRefresh){
			$this->markTestSkipped('TokenRefresh N/A');
			return;
		}

		$token = new AccessToken(['accessToken' => 'test_access_token', 'refreshToken' => 'test_refresh_token', 'expires' => 1]);
		$this->storage->storeAccessToken($this->provider->serviceName, $token);

		\sleep(2);

		$this->assertSame('such data! much wow!', Psr7\get_json($this->provider->request(''))->data);
	}

	public function testGetClientCredentials(){

		if(!$this->provider instanceof ClientCredentials){
			$this->markTestSkipped('ClientCredentials N/A');
			return;
		}

		$token = $this->provider->getClientCredentialsToken(['some_scope']);

		$this->assertSame('test_client_credentials_token', $token->accessToken);
		$this->assertGreaterThan(\time(), $token->expires);
	}

}

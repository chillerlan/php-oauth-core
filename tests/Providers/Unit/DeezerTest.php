<?php
/**
 * Class DeezerTest
 *
 * @created      09.08.2018
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2017 Smiley
 * @license      MIT
 */

namespace chillerlan\OAuthTest\Providers\Unit;

use chillerlan\OAuth\Core\{AccessToken, ProviderException};
use chillerlan\OAuth\Providers\Deezer;
use chillerlan\OAuthTest\Providers\OAuth2ProviderTestAbstract;
use function time;

/**
 * @property \chillerlan\OAuth\Providers\Deezer $provider
 */
class DeezerTest extends OAuth2ProviderTestAbstract{

	protected string $FQN = Deezer::class;

	protected array $testResponses = [
		'/oauth2/access_token' => 'access_token=test_access_token&expires_in=3600&state=test_state&scope=some_scope%20other_scope',
		'/oauth2/api/request'  => '{"data":"such data! much wow!"}',
	];

	public function testGetAuthURL():void{
		$this::assertStringContainsString(
			'https://connect.deezer.com/oauth/auth.php?app_id='.$this->options->key
				.'&foo=bar&perms=basic_access%20email&redirect_uri=https%3A%2F%2Flocalhost%2Fcallback&state=',
			(string)$this->provider->getAuthURL(
				['foo' => 'bar', 'client_secret' => 'not-so-secret'],
				[Deezer::SCOPE_BASIC, Deezer::SCOPE_EMAIL]
			)
		);
	}

	public function testParseTokenResponse():void{
		$token = $this->reflection
			->getMethod('parseTokenResponse')
			->invokeArgs($this->provider, [
				$this->responseFactory->createResponse()->withBody($this->streamFactory->createStream('access_token=whatever'))
			]);

		$this::assertInstanceOf(AccessToken::class, $token);
		$this::assertSame('whatever', $token->accessToken);
	}

	public function testParseTokenResponseErrorException():void{
		$this->expectException(ProviderException::class);
		$this->expectExceptionMessage('error retrieving access token:');

		$this->reflection
			->getMethod('parseTokenResponse')
			->invokeArgs($this->provider, [
				$this->responseFactory->createResponse()->withBody($this->streamFactory->createStream('error_reason=whatever'))
			]);
	}

	public function testParseTokenResponseNoDataException():void{
		$this::markTestSkipped('N/A');
	}

	public function testGetAccessToken():void{
		$token = $this->provider->getAccessToken('foo', 'test_state');

		$this::assertSame('test_access_token', $token->accessToken);
		$this::assertGreaterThan(time(), $token->expires);
	}

}

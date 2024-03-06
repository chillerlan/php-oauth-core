<?php
/**
 * Class SteamOpenIDTest
 *
 * @created      15.03.2021
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2021 smiley
 * @license      MIT
 */

namespace chillerlan\OAuthTest\Providers\Unit;

use chillerlan\HTTP\Utils\MessageUtil;
use chillerlan\OAuth\Core\AccessToken;
use chillerlan\OAuth\Providers\ProviderException;
use chillerlan\OAuth\Providers\SteamOpenID;
use chillerlan\OAuthTest\Providers\OAuthProviderTestAbstract;
use function urlencode;

/**
 * @property \chillerlan\OAuth\Providers\SteamOpenID $provider
 */
class SteamOpenIDTest extends OAuthProviderTestAbstract{

	protected const ID_VALID   = "ns:http://specs.openid.net/auth/2.0\x0ais_valid:true\x0a";
	protected const ID_INVALID = "ns:http://specs.openid.net/auth/2.0\x0ais_valid:false\x0a";

	protected string $FQN = SteamOpenID::class;

	protected array $testResponses = [
		'/steam/id'          => self::ID_VALID,
		'/steam/api/request' => '{"data":"such data! much wow!"}',
	];

	protected function setUp():void{
		parent::setUp();

		$this->provider->storeAccessToken(new AccessToken(['accessToken' => 'foo']));
		$this->reflection->getProperty('accessTokenURL')->setValue($this->provider, 'https://localhost/steam/id');
		$this->reflection->getProperty('apiURL')->setValue($this->provider, 'https://localhost/steam/api/request');
	}

	public function testGetAuthURL():void{
		$url = $this->provider->getAuthURL(['foo' => 'bar']);

		$expected = 'https://steamcommunity.com/openid/login'
			.'?openid.claimed_id=http%3A%2F%2Fspecs.openid.net%2Fauth%2F2.0%2Fidentifier_select'
			.'&openid.identity=http%3A%2F%2Fspecs.openid.net%2Fauth%2F2.0%2Fidentifier_select'
			.'&openid.mode=checkid_setup'
			.'&openid.ns=http%3A%2F%2Fspecs.openid.net%2Fauth%2F2.0'
			.'&openid.realm='.$this->options->key
			.'&openid.return_to='.urlencode($this->options->callbackURL);

		$this::assertSame($expected, (string)$url);
	}

	public function testGetAccessToken():void{

		$received = [
			'openid_ns'             => 'http://specs.openid.net/auth/2.0',
			'openid_mode'           => 'id_res',
			'openid_op_endpoint'    => 'https://steamcommunity.com/openid/login',
			'openid_claimed_id'     => 'https://steamcommunity.com/openid/id/69420',
			'openid_identity'       => 'https://steamcommunity.com/openid/id/69420',
			'openid_return_to'      => 'https://smiley.codes/oauth/',
			'openid_response_nonce' => '2021-03-16T06:40:46ZtLLZ4JqhLZ2IULBg8x2P8YitHQY=',
			'openid_assoc_handle'   => '1234567890',
			'openid_signed'         => 'signed,op_endpoint,claimed_id,identity,return_to,response_nonce,assoc_handle',
			'openid_sig'            => '7WEtj64YlaJLNqL6M0gZvVmOLFg=',
		];

		$this::assertSame('69420', $this->provider->getAccessToken($received)->accessToken);
	}

	public function testParseTokenResponse():void{
		$r = $this->responseFactory
			->createResponse()
			->withBody($this->streamFactory->createStream(self::ID_VALID))
		;

		$token = $this->reflection
			->getMethod('parseTokenResponse')
			->invokeArgs($this->provider, [$r]);

		$this::assertSame('SteamID', $token->accessToken);
	}

	public function testParseTokenResponseNoData():void{
		$this->expectException(ProviderException::class);
		$this->expectExceptionMessage('unable to parse token response');

		$this->reflection
			->getMethod('parseTokenResponse')
			->invokeArgs($this->provider, [$this->responseFactory->createResponse()]);
	}

	public function testParseTokenResponseInvalidID():void{
		$this->expectException(ProviderException::class);
		$this->expectExceptionMessage('invalid id');

		$r = $this->responseFactory
			->createResponse()
			->withBody($this->streamFactory->createStream(self::ID_INVALID))
		;

		$this->reflection
			->getMethod('parseTokenResponse')
			->invokeArgs($this->provider, [$r]);
	}

	// coverage
	public function testRequest():void{
		$r = $this->provider->request('');

		$this::assertSame('such data! much wow!', MessageUtil::decodeJSON($r)->data);
	}

}

<?php
/**
 *
 * @created      09.07.2017
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2017 Smiley
 * @license      MIT
 */

namespace chillerlan\OAuthTest\Core;

use chillerlan\OAuth\Core\AccessToken;
use PHPUnit\Framework\TestCase;

use function sleep, time;

class AccessTokenTest extends TestCase{

	protected AccessToken $token;

	protected function setUp():void{
		$this->token = new AccessToken;
	}

	public function tokenDataProvider():array{
		return [
			'accessTokenSecret'  => ['accessTokenSecret',  null, 'ACCESS_TOKEN'],
			'accessToken'        => ['accessToken',        null, 'ACCESS_TOKEN_SECRET'],
			'refreshToken'       => ['refreshToken',       null, 'REFRESH_TOKEN'],
			'extraParams'        => ['extraParams',        []  , ['foo' => 'bar']],
		];
	}

	/**
	 * @dataProvider tokenDataProvider
	 *
	 * @param $property
	 * @param $value
	 * @param $data
	 */
	public function testDefaultsGetSet($property, $value, $data):void{
		// test defaults
		static::assertSame($value, $this->token->{$property});

		// set some data
		$this->token->{$property} = $data;

		static::assertSame($data, $this->token->{$property});
	}

	public function expiryDataProvider():array{
		return [
			'EOL_UNKNOWN (null)'        => [null,       AccessToken::EOL_UNKNOWN],
			'EOL_UNKNOWN (-9001)'       => [-9001,      AccessToken::EOL_UNKNOWN],
			'EOL_UNKNOWN (-1)'          => [-1,         AccessToken::EOL_UNKNOWN],
			'EOL_UNKNOWN (1514309386)'  => [1514309386, AccessToken::EOL_UNKNOWN],
			'EOL_NEVER_EXPIRES (-9002)' => [-9002,      AccessToken::EOL_NEVER_EXPIRES],
			'EOL_NEVER_EXPIRES (0)'     => [0,          AccessToken::EOL_NEVER_EXPIRES],
		];
	}

	/**
	 * @dataProvider expiryDataProvider
	 *
	 * @param $expires
	 * @param $expected
	 */
	public function testSetExpiry($expires, $expected):void{
		$this->token->expires = $expires;

		static::assertSame($expected, $this->token->expires);
	}

	public function isExpiredDataProvider():array{
		return [
			'0 (f)'                 => [0,                              false],
			'EOL_NEVER_EXPIRES (f)' => [AccessToken::EOL_NEVER_EXPIRES, false],
			'EOL_UNKNOWN (f)'       => [AccessToken::EOL_UNKNOWN,       false],
		];
	}

	/**
	 * @dataProvider isExpiredDataProvider
	 *
	 * @param $expires
	 * @param $isExpired
	 */
	public function testIsExpired($expires, $isExpired):void{
		$this->token->setExpiry($expires);
		static::assertSame($isExpired, $this->token->isExpired());
	}

	public function testIsExpiredVariable():void{

		$now     = time();
		$expiry1 = $now + 3600;
		$this->token->setExpiry($expiry1);
		static::assertSame($expiry1, $this->token->expires);
		static::assertFalse($this->token->isExpired());

		$now     = time();
		$expiry2 = 3600;
		$this->token->setExpiry($expiry2);
		static::assertSame($now+$expiry2, $this->token->expires);
		static::assertFalse($this->token->isExpired());

		$now     = time();
		$expiry3 = 2;
		$this->token->setExpiry($expiry3);
		static::assertSame($now+$expiry3, $this->token->expires);
		sleep(3);
		static::assertTrue($this->token->isExpired());

		$now     = time();
		$expiry4 = $now + 2;
		$this->token->setExpiry($expiry4);
		static::assertSame($expiry4, $this->token->expires);
		sleep(3);
		static::assertTrue($this->token->isExpired());
	}

}

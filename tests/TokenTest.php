<?php
/**
 *
 * @filesource   TokenTest.php
 * @created      09.07.2017
 * @package      chillerlan\OAuthTest
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2017 Smiley
 * @license      MIT
 */

namespace chillerlan\OAuthTest;

use chillerlan\OAuth\Token;
use PHPUnit\Framework\TestCase;

class TokenTest extends TestCase{

	/**
	 * @var \chillerlan\OAuth\Token
	 */
	private $token;

	protected function setUp(){
		$this->token = new Token;
	}

	public function tokenDataProvider(){
		return [
			'requestToken'       => ['requestToken',       null, 'REQUEST_TOKEN'],
			'requestTokenSecret' => ['requestTokenSecret', null, 'REQUEST_TOKEN_SECRET'],
			'accessTokenSecret'  => ['accessTokenSecret',  null, 'ACCESS_TOKEN'],
			'accessToken'        => ['accessToken',        null, 'ACCESS_TOKEN_SECRET'],
			'refreshToken'       => ['refreshToken',       null, 'REFRESH_TOKEN'],
			'extraParams'        => ['extraParams',        []  , ['foo' => 'bar']],
		];
	}

	/**
	 * @dataProvider tokenDataProvider
	 */
	public function testDefaultsGetSet($property, $value, $data){
		// test defaults
		$this->assertSame($value, $this->token->{$property});

		// set some data
		$this->token->{$property} = $data;

		$this->assertSame($data, $this->token->{$property});
	}

	public function expiryDataProvider(){
		return [
			'EOL_UNKNOWN (null)'        => [null,       Token::EOL_UNKNOWN],
			'EOL_UNKNOWN (-9001)'       => [-9001,      Token::EOL_UNKNOWN],
			'EOL_UNKNOWN (-1)'          => [-1,         Token::EOL_UNKNOWN],
			'EOL_UNKNOWN (1514309386)'  => [1514309386, Token::EOL_UNKNOWN],
			'EOL_NEVER_EXPIRES (-9002)' => [-9002,      Token::EOL_NEVER_EXPIRES],
			'EOL_NEVER_EXPIRES (0)'     => [0,          Token::EOL_NEVER_EXPIRES],
		];
	}

	/**
	 * @dataProvider expiryDataProvider
	 */
	public function testSetExpiry($expires, $expected){
		$this->token->expires = $expires;

		$this->assertSame($expected, $this->token->expires);
	}

	public function isExpiredDataProvider(){
		return [
			'0 (f)'                 => [0,                        false],
			'EOL_NEVER_EXPIRES (f)' => [Token::EOL_NEVER_EXPIRES, false],
			'EOL_UNKNOWN (f)'       => [Token::EOL_UNKNOWN,       false],
		];
	}

	/**
	 * @dataProvider isExpiredDataProvider
	 */
	public function testIsExpired($expires, $isExpired){
		$this->token->setExpiry($expires);
		$this->assertSame($isExpired, $this->token->isExpired());
	}

	public function testIsExpiredVariable(){

		$now    = time();
		$expiry1 = $now + 3600;
		$this->token->setExpiry($expiry1);
		$this->assertSame($expiry1, $this->token->expires);
		$this->assertFalse($this->token->isExpired());

		$now    = time();
		$expiry2 = 3600;
		$this->token->setExpiry($expiry2);
		$this->assertSame($now+$expiry2, $this->token->expires);
		$this->assertFalse($this->token->isExpired());

		$now    = time();
		$expiry3 = 2;
		$this->token->setExpiry($expiry3);
		$this->assertSame($now+$expiry3, $this->token->expires);
		sleep(3);
		$this->assertTrue($this->token->isExpired());

		$now    = time();
		$expiry4 = $now + 2;
		$this->token->setExpiry($expiry4);
		$this->assertSame($expiry4, $this->token->expires);
		sleep(3);
		$this->assertTrue($this->token->isExpired());
	}

}

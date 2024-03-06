<?php
/**
 * Class NPROneAPITest
 *
 * @created      28.07.2019
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2019 smiley
 * @license      MIT
 */

namespace chillerlan\OAuthTest\Providers\Live;

use chillerlan\HTTP\Utils\MessageUtil;
use chillerlan\OAuth\Providers\NPROne;
use chillerlan\OAuth\Providers\ProviderException;
use chillerlan\OAuthTest\Providers\OAuth2APITestAbstract;

/**
 * @property \chillerlan\OAuth\Providers\NPROne $provider
 */
class NPROneAPITest extends OAuth2APITestAbstract{

	protected string $ENV = 'NPRONE';

	protected function getProviderFQCN():string{
		return NPROne::class;
	}

	public static function requestTargetProvider():array{
		return [
			'empty'          => ['', 'https://localhost/api'],
			'slash'          => ['/', 'https://localhost/api/'],
			'no slashes'     => ['a', 'https://localhost/api/a'],
			'leading slash'  => ['/b', 'https://localhost/api/b'],
			'trailing slash' => ['c/', 'https://localhost/api/c/'],
#			'full url given' => ['https://localhost/other/path/d', 'https://localhost/other/path/d'],
#			'ignore params'  => ['https://localhost/api/e/?with=param#foo', 'https://localhost/api/e/'],
		];
	}

	public function testMe():void{
		try{
			$this::assertSame($this->testuser, MessageUtil::decodeJSON($this->provider->me())->attributes->email);
		}
		catch(ProviderException){
			$this::markTestSkipped('token is missing or expired');
		}
	}

}

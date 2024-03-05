<?php
/**
 * Class VimeoAPITest
 *
 * @created      09.04.2018
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2018 Smiley
 * @license      MIT
 */

namespace chillerlan\OAuthTest\Providers\Live;

use chillerlan\HTTP\Utils\MessageUtil;
use chillerlan\OAuth\Providers\Vimeo;
use chillerlan\OAuthTest\Providers\OAuth2APITestAbstract;

/**
 * @property \chillerlan\OAuth\Providers\Vimeo $provider
 */
class VimeoAPITest extends OAuth2APITestAbstract{

	protected string $FQN = Vimeo::class;
	protected string $ENV = 'VIMEO';

	public function testMe():void{
		$this::assertSame('https://vimeo.com/'.$this->testuser, MessageUtil::decodeJSON($this->provider->me())->link);
	}

}

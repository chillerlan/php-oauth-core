<?php
/**
 * Class TwitterCCAPITest
 *
 * @created      26.10.2017
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2017 Smiley
 * @license      MIT
 */

namespace chillerlan\OAuthTest\Providers\Live;

use chillerlan\OAuth\Providers\TwitterCC;
use chillerlan\OAuthTest\Providers\OAuth2APITestAbstract;

/**
 * @property \chillerlan\OAuth\Providers\TwitterCC $provider
 */
class TwitterCCAPITest extends OAuth2APITestAbstract{

	protected string $ENV = 'TWITTER';

	protected function getProviderFQCN():string{
		return TwitterCC::class;
	}

	public function testMeErrorException():void{
		$this::markTestSkipped('not implemented');
	}

}

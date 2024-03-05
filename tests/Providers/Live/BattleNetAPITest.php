<?php
/**
 * Class BattleNetAPITest
 *
 * @created      03.08.2019
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2019 smiley
 * @license      MIT
 */

namespace chillerlan\OAuthTest\Providers\Live;

use chillerlan\HTTP\Utils\MessageUtil;
use chillerlan\OAuth\Providers\BattleNet;
use chillerlan\OAuthTest\Providers\OAuth2APITestAbstract;

/**
 * @property \chillerlan\OAuth\Providers\BattleNet $provider
 */
class BattleNetAPITest extends OAuth2APITestAbstract{

	protected string $FQN = BattleNet::class;
	protected string $ENV = 'BATTLENET';

	public function testMe():void{
		$this::assertSame($this->testuser, explode('#', MessageUtil::decodeJSON($this->provider->me())->battletag)[0]);
	}

}

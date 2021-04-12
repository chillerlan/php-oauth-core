<?php
/**
 * Class OAuthAPITestAbstract
 *
 * @created      08.09.2018
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2018 smiley
 * @license      MIT
 */

namespace chillerlan\OAuthTest\API;

use chillerlan\OAuth\OAuthOptions;
use chillerlan\OAuth\Storage\OAuthStorageInterface;
use chillerlan\OAuthTest\{OAuthTestHttpClient, OAuthTestMemoryStorage};
use chillerlan\OAuthTest\Providers\ProviderTestAbstract;
use chillerlan\Settings\SettingsContainerInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Log\LoggerInterface;

abstract class OAuthAPITestAbstract extends ProviderTestAbstract{

	protected string $CFG;

	/** a test username for live API tests, defined in .env as {ENV-PREFIX}_TESTUSER*/
	protected string $testuser;

	protected function setUp():void{
		parent::setUp();

		if($this->is_ci){
			$this->markTestSkipped('not on CI (set TEST_IS_CI in phpunit.xml to "false" if you want to run live API tests)');
		}

	}

	protected function initStorage(SettingsContainerInterface $options):OAuthStorageInterface{
		return new OAuthTestMemoryStorage($options, $this->CFG);
	}

	protected function initHttp(SettingsContainerInterface $options, LoggerInterface $logger, array $responses):ClientInterface{
		return new OAuthTestHttpClient($this->CFG, $logger);
	}

}

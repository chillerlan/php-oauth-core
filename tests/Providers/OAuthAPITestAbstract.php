<?php
/**
 * Class OAuthAPITestAbstract
 *
 * @created      02.08.2019
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2019 smiley
 * @license      MIT
 */

namespace chillerlan\OAuthTest\Providers;

use chillerlan\DotEnv\DotEnv;
use chillerlan\OAuth\OAuthOptions;
use chillerlan\OAuth\Core\AccessToken;
use chillerlan\OAuth\Providers\ProviderException;
use chillerlan\OAuth\Storage\{MemoryStorage, OAuthStorageInterface};
use chillerlan\OAuthTest\Helpers\{OAuthTestHttpClient, OAuthTestMemoryStorage};
use chillerlan\Settings\SettingsContainerInterface;
use Exception;
use Psr\Http\Client\ClientInterface;
use Psr\Log\LoggerInterface;
use function constant, defined, realpath;

/**
 * @property \chillerlan\OAuth\Core\OAuthInterface $provider
 */
abstract class OAuthAPITestAbstract extends OAuthProviderTestAbstract{

	protected DotEnv $dotEnv;
	protected string $ENV;
	protected string $CFG;

	/** a test username for live API tests, defined in .env as {ENV-PREFIX}_TESTUSER*/
	protected string $testuser;

	/**
	 * @throws \Exception
	 */
	protected function setUp():void{

		foreach(['TEST_CFGDIR', 'TEST_ENVFILE'] as $constant){
			if(!defined($constant)){
				throw new Exception($constant.' not set -> see phpunit.xml');
			}
		}

		// set the config dir and .env config before initializing the provider
		$this->CFG      = realpath(__DIR__.'/../../'.constant('TEST_CFGDIR'));
		$this->dotEnv   = (new DotEnv($this->CFG, constant('TEST_ENVFILE'), false))->load();
		$this->testuser = (string)$this->dotEnv->get($this->ENV.'_TESTUSER');

		// init provider etc.
		parent::setUp();

		// is_ci is now set
		if($this->is_ci){
			$this->markTestSkipped('not on CI (set TEST_IS_CI in phpunit.xml to "false" if you want to run live API tests)');
		}

	}

	protected function initOptions():SettingsContainerInterface{
		return new OAuthOptions([
			'key'              => ($this->dotEnv->get($this->ENV.'_KEY') ?? ''),
			'secret'           => ($this->dotEnv->get($this->ENV.'_SECRET') ?? ''),
			'tokenAutoRefresh' => true,
		]);
	}

	protected function initStorage(SettingsContainerInterface $options):OAuthStorageInterface{
		return new OAuthTestMemoryStorage($options, $this->CFG);
	}

	protected function initHttp(SettingsContainerInterface $options, LoggerInterface $logger, array $responses):ClientInterface{
		return new OAuthTestHttpClient($this->CFG, $logger);
	}

	public function testTokenInvalidate():void{
		$this::markTestSkipped('TokenInvalidate N/A on Live Test');
	}

	public function testMe():void{
		$this::markTestSkipped('not implemented');
	}

	public function testMeErrorException():void{
		$this::expectException(ProviderException::class);
		$token                    = $this->storage->getAccessToken($this->provider->serviceName);
		// avoid refresh
		$token->expires           = AccessToken::EOL_NEVER_EXPIRES;
		// invalidate token
		$token->accessToken       = 'nope';
		$token->accessTokenSecret = 'what';

		// using a temp storage here so that the local tokens won't be overwritten
		$tempStorage = (new MemoryStorage)->storeAccessToken($token, $this->provider->serviceName);

		$this->provider
			->setStorage($tempStorage)
			->me()
		;
	}

}

<?php
/**
 * Class OAuthTestAbstract
 *
 * @filesource   OAuthTestAbstract.php
 * @created      21.04.2018
 * @package      chillerlan\OAuthTest
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2018 smiley
 * @license      MIT
 */

namespace chillerlan\OAuthTest;

use chillerlan\Logger\Log;
use chillerlan\Logger\LogOptions;
use chillerlan\Logger\Output\LogOutputAbstract;
use chillerlan\DotEnv\DotEnv;
use PHPUnit\Framework\TestCase;

abstract class OAuthTestAbstract extends TestCase{

	/**
	 * @var string
	 */
	protected $CFGDIR    = __DIR__.'/../config';

	/**
	 * @var string
	 */
	protected $envvar;

	/**
	 * determines whether the tests run on Travis CI or not -> .env IS_CI=TRUE
	 *
	 * @var bool
	 */
	protected $isCI;

	/**
	 * @var \chillerlan\OAuth\OAuthOptions
	 */
	protected $options;

	/**
	 * @var \chillerlan\DotEnv\DotEnv
	 */
	protected $env;

	/**
	 * @var \Psr\Log\LoggerInterface
	 */
	protected $logger;

	protected function setUp(){
		ini_set('date.timezone', 'Europe/Amsterdam');

		$this->env  = (new DotEnv($this->CFGDIR, file_exists($this->CFGDIR.'/.env') ? '.env' : '.env_travis'))->load();
		$this->isCI = $this->env->get('IS_CI') === 'TRUE';

		$logger = new Log;

		// no log spam on travis
		if(!$this->isCI){

			$logger->addInstance(
				new class (new LogOptions(['minLogLevel' => 'debug'])) extends LogOutputAbstract{

					protected function __log(string $level, string $message, array $context = null):void{
						echo $message.PHP_EOL.print_r($context, true).PHP_EOL;
					}

				},
				'console'
			);

		}

		$this->logger = $logger;
	}

}

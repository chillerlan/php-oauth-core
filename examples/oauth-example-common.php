<?php
/**
 * @filesource   oauth-example-common.php
 * @created      09.04.2018
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2018 smiley
 * @license      MIT
 */

use chillerlan\Database\{
	Database, DatabaseOptionsTrait, Drivers\MySQLiDrv
};
use chillerlan\HTTP\{
	HTTPClientAbstract, HTTPResponseInterface, TinyCurlClient
};
use chillerlan\Logger\{
	Log, LogOptions, LogOptionsTrait, LogTrait, Output\LogOutputAbstract
};
use chillerlan\OAuth\{
	OAuthOptions, Storage\SessionTokenStorage
};
use chillerlan\TinyCurl\Request;
use chillerlan\Traits\{
	ContainerInterface, DotEnv
};
use Psr\Log\NullLogger;

ini_set('date.timezone', 'Europe/Amsterdam');

/** @var string $ENVVAR */
/** @var string $CFGDIR */

$env = (new DotEnv($CFGDIR, '.env', false))->load();

$options_arr = [
	// OAuthOptions
	'key'              => $env->get($ENVVAR.'_KEY'),
	'secret'           => $env->get($ENVVAR.'_SECRET'),
	'callbackURL'      => $env->get($ENVVAR.'_CALLBACK_URL'),
	'dbUserID'         => 1,
	'dbTokenTable'     => 'storagetest',
	'dbProviderTable'  => 'storagetest_providers',
	'storageCryptoKey' => '000102030405060708090a0b0c0d0e0f101112131415161718191a1b1c1d1e1f',
	'tokenAutoRefresh' => true,

	// DatabaseOptions
	'driver'           => MySQLiDrv::class,
	'host'             => $env->MYSQL_HOST,
	'port'             => $env->MYSQL_PORT,
	'database'         => $env->MYSQL_DATABASE,
	'username'         => $env->MYSQL_USERNAME,
	'password'         => $env->MYSQL_PASSWORD,

	// HTTPOptions
	'ca_info'          => $CFGDIR.'/cacert.pem',
	'userAgent'        => 'chillerlanPhpOAuth/3.0.0 +https://github.com/codemasher/php-oauth',

	// log
	'minLogLevel'      => 'debug',
];

/** @var \chillerlan\Traits\ContainerInterface $options */
$options = new class($options_arr) extends OAuthOptions{
	use DatabaseOptionsTrait, LogOptionsTrait;
};

/** @var \Psr\Log\LoggerInterface $logger */
$logger = (new Log)->addInstance(
	new class ($options) extends LogOutputAbstract{

		protected function __log(string $level, string $message, array $context = null):void{
			echo $message.PHP_EOL;

			if(!empty($context)){
				echo print_r($context, true).PHP_EOL;
			}
		}

	},
	'console'
);

/** @var \chillerlan\HTTP\HTTPClientInterface $http */
$http = new class($options) extends HTTPClientAbstract{
	use LogTrait;

	protected $client;

	public function __construct(ContainerInterface $options){
		parent::__construct($options);
		$this->client = new TinyCurlClient($this->options, new Request($this->options));
	}

	public function request(string $url, array $params = null, string $method = null, $body = null, array $headers = null):HTTPResponseInterface{
		$args = func_get_args();
		$this->debug('$args', $args);

		$response = $this->client->request(...$args);
		$this->debug(print_r($response, true));

		usleep(100000); // flood protection
		return $response;
	}

};

$http->setLogger(new NullLogger);

/** @var \chillerlan\Database\Database $db */
$db = new Database($options);
$db->setLogger(new NullLogger);

/** @var \chillerlan\OAuth\Storage\TokenStorageInterface $storage */
$storage = new SessionTokenStorage($options); //new DBTokenStorage($options, $db);
$storage->setLogger(new NullLogger);

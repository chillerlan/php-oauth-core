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
	HTTPClientAbstract, HTTPResponseInterface, CurlClient
};
use chillerlan\Logger\{
	Log, LogOptionsTrait, Output\ConsoleLog
};
use chillerlan\OAuth\{
	OAuthOptions, Storage\SessionStorage
};
use chillerlan\Traits\{
	ContainerInterface, DotEnv
};

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

	// test http client
	'sleep'            => 0.25,
];

/** @var \chillerlan\Traits\ContainerInterface $options */
$options = new class($options_arr) extends OAuthOptions{
	use DatabaseOptionsTrait, LogOptionsTrait;

	protected $sleep;
};

$logger = new Log;
$logger->addInstance(new ConsoleLog($options), 'console');

/** @var \chillerlan\HTTP\HTTPClientInterface $http */
$http = new class($options) extends HTTPClientAbstract{

	protected $client;

	public function __construct(ContainerInterface $options){
		parent::__construct($options);
		$this->client = new CurlClient($this->options);
	}

	protected function getResponse():HTTPResponseInterface{

		$this->logger->debug('$args', [
			'$url' => $this->requestURL,
			'$params' => $this->requestParams,
			'$method' => $this->requestMethod,
			'$body' => $this->requestBody,
			'$headers' => $this->requestHeaders,
		]);

		$response = $this->client->request($this->requestURL, $this->requestParams, $this->requestMethod, $this->requestBody, $this->requestHeaders);

		$this->logger->debug($response->body, (array)$response->headers);

		usleep($this->options->sleep * 1000000);

		return $response;
	}

};

#$http->setLogger($logger);

/** @var \chillerlan\Database\Database $db */
$db = new Database($options);
#$db->setLogger($logger);

/** @var \chillerlan\OAuth\Storage\OAuthStorageInterface $storage */
$storage = new SessionStorage($options); //new DBStorage($options, $db);
#$storage->setLogger($logger);

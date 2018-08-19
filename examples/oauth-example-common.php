<?php
/**
 * @filesource   oauth-example-common.php
 * @created      09.04.2018
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2018 smiley
 * @license      MIT
 */

use chillerlan\HTTP\{CurlClient, HTTPClientAbstract, HTTPResponseInterface};
use chillerlan\Logger\{Log, LogOptionsTrait, Output\ConsoleLog};
use chillerlan\OAuth\{OAuthOptions, Storage\SessionStorage};
use chillerlan\Traits\{DotEnv, ImmutableSettingsInterface};

ini_set('date.timezone', 'Europe/Amsterdam');

/** @var string $ENVVAR */
/** @var string $CFGDIR */

$env = (new DotEnv($CFGDIR, '.env', false))->load();

$options_arr = [
	// OAuthOptions
	'key'              => $env->get($ENVVAR.'_KEY'),
	'secret'           => $env->get($ENVVAR.'_SECRET'),
	'callbackURL'      => $env->get($ENVVAR.'_CALLBACK_URL'),
	'tokenAutoRefresh' => true,

	// HTTPOptions
	'ca_info'          => $CFGDIR.'/cacert.pem',
	'userAgent'        => 'chillerlanPhpOAuth/3.0.0 +https://github.com/codemasher/php-oauth',

	// log
	'minLogLevel'      => 'debug',

	// test http client
	'sleep'            => 0.25,
];

/** @var \chillerlan\Traits\ImmutableSettingsInterface $options */
$options = new class($options_arr) extends OAuthOptions{
	use LogOptionsTrait;

	protected $sleep;
};

$logger = new Log;
$logger->addInstance(new ConsoleLog($options), 'console');

/** @var \chillerlan\HTTP\HTTPClientInterface $http */
$http = new class($options) extends HTTPClientAbstract{

	protected $client;

	public function __construct(ImmutableSettingsInterface $options){
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

/** @var \chillerlan\OAuth\Storage\OAuthStorageInterface $storage */
$storage = new SessionStorage($options);
#$storage->setLogger($logger);

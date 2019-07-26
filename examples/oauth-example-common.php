<?php
/**
 * @filesource   oauth-example-common.php
 * @created      09.04.2018
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2018 smiley
 * @license      MIT
 */

use chillerlan\HTTP\Psr18\CurlClient;
use chillerlan\OAuth\OAuthOptions;
use chillerlan\DotEnv\DotEnv;
use chillerlan\OAuthTest\{ExampleStorage, OAuthTestLogger};

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
];

/** @var \chillerlan\Settings\SettingsContainerInterface $options */
$options = new class($options_arr) extends OAuthOptions{
	protected $sleep;
};

$logger = new OAuthTestLogger('debug');

/** @var \chillerlan\HTTP\Psr18\HTTPClientInterface $http */
$http = new CurlClient($options);

/** @var \chillerlan\OAuth\Storage\OAuthStorageInterface $storage */
$storage = new ExampleStorage($options, $CFGDIR);
#$storage->setLogger($logger);

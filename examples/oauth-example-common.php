<?php
/**
 * @filesource   oauth-example-common.php
 * @created      09.04.2018
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2018 smiley
 * @license      MIT
 */

use chillerlan\OAuth\OAuthOptions;
use chillerlan\DotEnv\DotEnv;
use chillerlan\OAuthTest\{OAuthTestHttpClient, OAuthTestSessionStorage, OAuthTestLogger};

\ini_set('date.timezone', 'Europe/Amsterdam');

// these vars are supposed to be set before this file is included
/** @var string $ENVVAR - name prefix for the environment variable */
/** @var string $CFGDIR - the directory where configuration is stored (.env, cacert, tokens) */

$LOGLEVEL = $LOGLEVEL ?? 'info';

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

	// testHTTPClient
	'sleep'            => 0.25,
];

/** @var \chillerlan\Settings\SettingsContainerInterface $options */
$options = new class($options_arr) extends OAuthOptions{
	protected $sleep; // testHTTPClient
};

$logger = new OAuthTestLogger($LOGLEVEL);

/** @var \chillerlan\HTTP\Psr18\HTTPClientInterface $http */
$http = new OAuthTestHttpClient($options);
#$http->setLogger($logger);

/** @var \chillerlan\OAuth\Storage\OAuthStorageInterface $storage */
$storage = new OAuthTestSessionStorage($options, $CFGDIR);
#$storage->setLogger($logger);

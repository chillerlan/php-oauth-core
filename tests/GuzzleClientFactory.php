<?php
/**
 * Class GuzzleClientFactory
 *
 * requires Guzzle >= 7.3 (and Guzzle PSR-7 >= 2.0 for the PSR-17 factories)
 *
 * @created      01.04.2021
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2021 smiley
 * @license      MIT
 *
 * @noinspection ALL
 */

namespace chillerlan\OAuthTest;

use GuzzleHttp\Client;
use Psr\Http\Client\ClientInterface;

class GuzzleClientFactory implements OAuthTestHttpClientFactoryInterface{

	public static function getClient(string $cfgdir):ClientInterface{
		return new Client([
			'verify'  => $cfgdir.'/cacert.pem',
			'headers' => [
				'User-Agent' => 'chillerlanPhpOAuth/4.0.0 +https://github.com/chillerlan/php-oauth-core',
			],
		]);
	}

}

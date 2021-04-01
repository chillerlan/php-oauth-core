<?php
/**
 * Class GuzzleClientFactory
 *
 * @created      01.04.2021
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2021 smiley
 * @license      MIT
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

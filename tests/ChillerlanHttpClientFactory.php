<?php
/**
 * Class ChillerlanHttpClientFactory
 *
 * @created      01.04.2021
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2021 smiley
 * @license      MIT
 */

namespace chillerlan\OAuthTest;

use chillerlan\HTTP\HTTPOptions;
use chillerlan\HTTP\Psr18\CurlClient;
use Psr\Http\Client\ClientInterface;

class ChillerlanHttpClientFactory implements OAuthTestHttpClientFactoryInterface{

	/**
	 * @inheritDoc
	 */
	public static function getClient(string $cfgdir):ClientInterface{
		$options             = new HTTPOptions;
		$options->ca_info    = $cfgdir.'/cacert.pem';
		$options->user_agent = 'chillerlanPhpOAuth/4.0.0 +https://github.com/chillerlan/php-oauth-core';

		return new CurlClient($options);
	}

}

<?php
/**
 * Interface OAuthTestHttpClientFactoryInterface
 *
 * @created      01.04.2021
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2021 smiley
 * @license      MIT
 */

namespace chillerlan\OAuthTest;

use Psr\Http\Client\ClientInterface;

interface OAuthTestHttpClientFactoryInterface{

	/**
	 * Returns a fully prepared http client instance
	 */
	public static function getClient(string $cfgdir):ClientInterface;

}

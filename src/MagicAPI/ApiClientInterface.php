<?php
/**
 * Interface ApiClientInterface
 *
 * @created      01.09.2018
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2018 smiley
 * @license      MIT
 */

namespace chillerlan\OAuth\MagicAPI;

use Psr\Http\Message\ResponseInterface;

interface ApiClientInterface{

	/**
	 * Implements the Magic API client
	 */
	public function __call(string $endpointName, array $arguments):ResponseInterface;

}

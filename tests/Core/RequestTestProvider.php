<?php
/**
 * Class RequestTestProvider
 *
 * @created      17.03.2021
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2021 smiley
 * @license      MIT
 */
declare(strict_types=1);

namespace chillerlan\OAuthTest\Core;

use chillerlan\OAuth\Core\{AccessToken, OAuthProvider};
use Exception;
use Psr\Http\Message\{RequestInterface, UriInterface};

final class RequestTestProvider extends OAuthProvider{

	protected string $apiURL = 'https://localhost';

	/**
	 * @throws \Exception
	 */
	public function getAuthURL(array $params = null):UriInterface{
		throw new Exception('should not happen (unused method)');
	}

	public function getRequestAuthorization(RequestInterface $request, AccessToken $token):RequestInterface{
		return $request; // just return the original request
	}

}
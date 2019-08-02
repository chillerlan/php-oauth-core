<?php
/**
 * Class ProviderTestHttpClient
 *
 * @filesource   ProviderTestHttpClient.php
 * @created      02.08.2019
 * @package      chillerlan\OAuthTest\Providers
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2019 smiley
 * @license      MIT
 */

namespace chillerlan\OAuthTest\Providers;

use chillerlan\HTTP\Psr7;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\{RequestInterface, ResponseInterface};
use Psr\Log\LoggerInterface;

abstract class ProviderTestHttpClient implements ClientInterface{

	/** @var array */
	protected $responses;
	/** @var \Psr\Log\LoggerInterface */
	protected $logger;

	public function __construct(array $responses, LoggerInterface $logger){
		$this->responses = $responses;
		$this->logger    = $logger;
	}

	protected function logRequest(RequestInterface $request, ResponseInterface $response):ResponseInterface{
		$this->logger->debug("\n-----REQUEST------\n".Psr7\message_to_string($request));
		$this->logger->debug("\n-----RESPONSE-----\n".Psr7\message_to_string($response));

		$response->getBody()->rewind();

		return $response;
	}

}

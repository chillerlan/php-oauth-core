<?php
/**
 * Class OAuthTestHttpClient
 *
 * @filesource   OAuthTestHttpClient.php
 * @created      26.07.2019
 * @package      chillerlan\OAuthTest
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2019 smiley
 * @license      MIT
 */

namespace chillerlan\OAuthTest;

use chillerlan\HTTP\{Psr18\CurlClient, Psr7};
use chillerlan\Settings\SettingsContainerInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\{RequestInterface, ResponseInterface};
use Psr\Log\{LoggerAwareInterface, LoggerAwareTrait, LoggerInterface, NullLogger};

class OAuthTestHttpClient implements ClientInterface, LoggerAwareInterface{
	use LoggerAwareTrait;

	/**
	 * @var \chillerlan\Settings\SettingsContainerInterface
	 */
	protected $options;

	/**
	 * @var \Psr\Http\Client\ClientInterface
	 */
	protected $client;

	/**
	 * OAuthTestHttpClient constructor.
	 *
	 * @param \chillerlan\Settings\SettingsContainerInterface $options
	 * @param \Psr\Http\Client\ClientInterface|null           $http
	 * @param \Psr\Log\LoggerInterface|null                   $logger
	 */
	public function __construct(SettingsContainerInterface $options, ClientInterface $http = null, LoggerInterface $logger = null){
		$this->options = $options;
		$this->client  = $http ?? new CurlClient($this->options);

		$this->setLogger($logger ?? new NullLogger);
	}

	/**
	 * @param \Psr\Http\Message\RequestInterface $request
	 *
	 * @return \Psr\Http\Message\ResponseInterface
	 */
	public function sendRequest(RequestInterface $request):ResponseInterface{
		usleep($this->options->sleep * 1000000);

		$response = $this->client->sendRequest($request);

		$this->logger->debug("\n-----REQUEST------\n".Psr7\message_to_string($request));
		$this->logger->debug("\n-----RESPONSE-----\n".Psr7\message_to_string($response));

		$response->getBody()->rewind();
		return $response;
	}

}

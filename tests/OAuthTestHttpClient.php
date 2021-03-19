<?php
/**
 * Class OAuthTestHttpClient
 *
 * @created      26.07.2019
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2019 smiley
 * @license      MIT
 */

namespace chillerlan\OAuthTest;

use chillerlan\HTTP\Psr18\{CurlClient, LoggingClient};
use chillerlan\Settings\SettingsContainerInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\{RequestInterface, ResponseInterface};
use Psr\Log\{LoggerAwareInterface, LoggerInterface, NullLogger};

use function usleep;

class OAuthTestHttpClient implements ClientInterface, LoggerAwareInterface{

	protected SettingsContainerInterface $options;

	protected ClientInterface $client;

	public function __construct(
		SettingsContainerInterface $options,
		ClientInterface $http = null,
		LoggerInterface $logger = null
	){
		$this->options = $options;
		$this->client  = new LoggingClient(
			$http ?? new CurlClient($this->options),
			$logger ?? new NullLogger
		);
	}

	/**
	 * @inheritDoc
	 */
	public function setLogger(LoggerInterface $logger):void{
		$this->client->setLogger($logger);
	}

	/**
	 * @inheritDoc
	 */
	public function sendRequest(RequestInterface $request):ResponseInterface{
		usleep($this->options->sleep * 1000000);

		return $this->client->sendRequest($request);
	}

}

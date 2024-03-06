<?php
/**
 * Class OAuthTestHttpClient
 *
 * @created      26.07.2019
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2019 smiley
 * @license      MIT
 */

namespace chillerlan\OAuthTest\Helpers;

use chillerlan\HTTP\Utils\MessageUtil;
use Psr\Http\Client\{ClientExceptionInterface, ClientInterface};
use Psr\Http\Message\{RequestInterface, ResponseInterface};
use Psr\Log\{LoggerInterface, NullLogger};
use Exception, Throwable;
use function class_exists, constant, defined, get_class, sprintf, usleep;

final class OAuthTestHttpClient implements ClientInterface{

	protected ClientInterface $http;

	/**
	 * @throws \Exception
	 */
	public function __construct(
		string $cfgdir,
		protected LoggerInterface $logger = new NullLogger
	){

		if(!defined('TEST_CLIENT_FACTORY')){
			throw new Exception('TEST_CLIENT_FACTORY in phpunit.xml not set');
		}

		$clientFactory = constant('TEST_CLIENT_FACTORY');

		if(!class_exists($clientFactory)){
			throw new Exception(sprintf('invalid class: "%s"', $clientFactory));
		}

		$this->http = $clientFactory::getClient($cfgdir);
	}

	/**
	 * @inheritDoc
	 */
	public function sendRequest(RequestInterface $request):ResponseInterface{
		$this->logger->debug("\n----HTTP-REQUEST----\n".MessageUtil::toString($request));
		usleep(250000);

		try{
			$response = $this->http->sendRequest($request);
		}
		catch(Throwable $e){
			$this->logger->debug("\n----HTTP-ERROR------\n");
			$this->logger->error($e->getMessage());
			$this->logger->error($e->getTraceAsString());

			if(!$e instanceof ClientExceptionInterface){
				throw new Exception('unexpected exception, does not implement "ClientExceptionInterface": '.get_class($e));
			}

			throw $e;
		}

		$this->logger->debug("\n----HTTP-RESPONSE---\n".MessageUtil::toString($response));

		return $response;
	}

}

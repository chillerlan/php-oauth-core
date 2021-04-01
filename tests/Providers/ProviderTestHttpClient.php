<?php
/**
 * Class ProviderTestHttpClient
 *
 * @created      17.03.2021
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2021 smiley
 * @license      MIT
 */

namespace chillerlan\OAuthTest\Providers;

use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\{RequestInterface, ResponseFactoryInterface, ResponseInterface, StreamFactoryInterface};

use function strpos;

/**
 * a dummy client that returns a prepared set of responses
 */
class ProviderTestHttpClient implements ClientInterface{

	protected array $responses;
	protected ResponseFactoryInterface $responseFactory;
	protected StreamFactoryInterface $streamFactory;

	public function __construct(
		array $responses,
		ResponseFactoryInterface $responseFactory,
		StreamFactoryInterface $streamFactory
	){
		$this->responses = $responses;
		$this->responseFactory = $responseFactory;
		$this->streamFactory = $streamFactory;
	}

	/**
	 * @inheritDoc
	 */
	public function sendRequest(RequestInterface $request):ResponseInterface{
		$path = $request->getUri()->getPath();

		// echo the request
		if(strpos($path, OAuthProviderTestAbstract::ECHO_REQUEST) !== false){
			$response = $this->responseFactory->createResponse()->withHeader('x-request-uri', (string)$request->getUri());

			foreach($request->getHeaders() as $header => $values){
				foreach($values as $value){
					$response = $response->withHeader($header, $value);
				}
			}

			return $response->withBody($request->getBody());
		}

		// nope
		if(!isset($this->responses[$path])){
			return $this->responseFactory->createResponse(404);
		}

		return $this->responseFactory->createResponse()->withBody($this->streamFactory->createStream($this->responses[$path]));
	}

}

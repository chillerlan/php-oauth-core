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

use chillerlan\HTTP\Psr7\Response;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\{RequestInterface, ResponseInterface};

use function chillerlan\HTTP\Psr17\create_stream_from_input;
use function strpos;

/**
 * a dummy client that returns a prepared set of responses
 */
class ProviderTestHttpClient implements ClientInterface{

	protected array $responses;

	public function __construct(array $responses){
		$this->responses = $responses;
	}

	/**
	 * @inheritDoc
	 */
	public function sendRequest(RequestInterface $request):ResponseInterface{
		$path = $request->getUri()->getPath();

		// echo the request
		if(strpos($path, OAuthProviderTestAbstract::ECHO_REQUEST) !== false){
			$response = (new Response)->withHeader('x-request-uri', (string)$request->getUri());

			foreach($request->getHeaders() as $header => $values){
				foreach($values as $value){
					$response = $response->withHeader($header, $value);
				}
			}

			return $response->withBody($request->getBody());
		}

		// nope
		if(!isset($this->responses[$path])){
			return new Response(404);
		}

		return (new Response)->withBody(create_stream_from_input($this->responses[$path]));
	}

}

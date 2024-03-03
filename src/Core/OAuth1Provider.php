<?php
/**
 * Class OAuth1Provider
 *
 * @created      09.07.2017
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2017 Smiley
 * @license      MIT
 */
declare(strict_types=1);

namespace chillerlan\OAuth\Core;

use chillerlan\HTTP\Utils\{MessageUtil, QueryUtil};
use Psr\Http\Message\{RequestInterface, ResponseInterface, UriInterface};
use function array_merge, base64_encode, hash_hmac, implode, in_array, random_bytes, sodium_bin2hex, strtoupper, time;

/**
 * Implements an abstract OAuth1 provider with all methods required by the OAuth1Interface.

 *  @see https://datatracker.ietf.org/doc/html/rfc5849
 */
abstract class OAuth1Provider extends OAuthProvider implements OAuth1Interface{

	/**
	 * The request OAuth1 token URL
	 */
	protected string $requestTokenURL;

	/**
	 * @inheritDoc
	 */
	public function getAuthURL(array|null $params = null):UriInterface{
		$params = array_merge(($params ?? []), ['oauth_token' => $this->getRequestToken()->accessToken]);

		return $this->uriFactory->createUri(QueryUtil::merge($this->authURL, $params));
	}

	/**
	 * @inheritDoc
	 */
	public function getRequestToken():AccessToken{

		$params = [
			'oauth_callback'         => $this->options->callbackURL,
			'oauth_consumer_key'     => $this->options->key,
			'oauth_nonce'            => $this->nonce(),
			'oauth_signature_method' => 'HMAC-SHA1',
			'oauth_timestamp'        => time(),
			'oauth_version'          => '1.0',
		];

		$params['oauth_signature'] = $this->getSignature($this->requestTokenURL, $params, 'POST');

		$request = $this->requestFactory
			->createRequest('POST', $this->requestTokenURL)
			->withHeader('Authorization', 'OAuth '.QueryUtil::build($params, null, ', ', '"'))
			->withHeader('Accept-Encoding', 'identity') // try to avoid compression
			->withHeader('Content-Length', '0') // tumblr requires a content-length header set
		;

		foreach($this->authHeaders as $header => $value){
			$request = $request->withAddedHeader($header, $value);
		}

		return $this->parseTokenResponse($this->http->sendRequest($request), true);
	}

	/**
	 * Parses the response from a request to the token endpoint
	 *
	 * @see https://datatracker.ietf.org/doc/html/rfc5849#section-2.1
	 * @see https://datatracker.ietf.org/doc/html/rfc5849#section-2.3
	 *
	 * @throws \chillerlan\OAuth\Core\ProviderException
	 */
	protected function parseTokenResponse(ResponseInterface $response, bool $checkCallbackConfirmed):AccessToken{
		$data = QueryUtil::parse(MessageUtil::decompress($response));

		if(empty($data)){
			throw new ProviderException('unable to parse token response');
		}
		elseif(isset($data['error'])){
			throw new ProviderException('error retrieving access token: '.$data['error']);
		}
		elseif(!isset($data['oauth_token']) || !isset($data['oauth_token_secret'])){
			throw new ProviderException('invalid token');
		}

		if(
			$checkCallbackConfirmed
			&& (!isset($data['oauth_callback_confirmed']) || $data['oauth_callback_confirmed'] !== 'true')
		){
			throw new ProviderException('oauth callback unconfirmed');
		}

		$token = $this->createAccessToken();

		$token->accessToken       = $data['oauth_token'];
		$token->accessTokenSecret = $data['oauth_token_secret'];
		$token->expires           = AccessToken::EOL_NEVER_EXPIRES;

		unset($data['oauth_token'], $data['oauth_token_secret']);

		$token->extraParams = $data;

		$this->storage->storeAccessToken($token, $this->serviceName);

		return $token;
	}

	/**
	 * returns a 32 byte random string (in hexadecimal representation) for use as a nonce
	 *
	 * @see https://datatracker.ietf.org/doc/html/rfc5849#section-3.3
	 */
	protected function nonce():string{
		return sodium_bin2hex(random_bytes(32));
	}

	/**
	 * Generates a request signature
	 *
	 * @see https://datatracker.ietf.org/doc/html/rfc5849#section-3.4
	 *
	 * @throws \chillerlan\OAuth\Core\ProviderException
	 */
	protected function getSignature(string $url, array $params, string $method, string|null $accessTokenSecret = null):string{
		$parsed = $this->uriFactory->createUri($url);

		if($parsed->getHost() == '' || $parsed->getScheme() === '' || !in_array($parsed->getScheme(), ['http', 'https'])){
			throw new ProviderException('getSignature: invalid url');
		}

		$signatureParams = array_merge(QueryUtil::parse($parsed->getQuery()), $params);
		$url             = (string)$parsed->withQuery('')->withFragment('');

		unset($signatureParams['oauth_signature']);

		// https://datatracker.ietf.org/doc/html/rfc5849#section-3.4.1.1
		$data = QueryUtil::recursiveRawurlencode([strtoupper($method), $url, QueryUtil::build($signatureParams)]);

		// https://datatracker.ietf.org/doc/html/rfc5849#section-3.4.2
		$key  = QueryUtil::recursiveRawurlencode([$this->options->secret, ($accessTokenSecret ?? '')]);

		return base64_encode(hash_hmac('sha1', implode('&', $data), implode('&', $key), true));
	}

	/**
	 * @inheritDoc
	 */
	public function getAccessToken(string $token, string $verifier):AccessToken{

		$request = $this->requestFactory
			->createRequest('POST', QueryUtil::merge($this->accessTokenURL, ['oauth_verifier' => $verifier]))
			->withHeader('Accept-Encoding', 'identity')
			->withHeader('Content-Length', '0')
		;

		$request = $this->getRequestAuthorization($request, $this->storage->getAccessToken($this->serviceName));

		return $this->parseTokenResponse($this->http->sendRequest($request), false);
	}

	/**
	 * @inheritDoc
	 */
	public function getRequestAuthorization(RequestInterface $request, AccessToken $token):RequestInterface{
		$uri   = $request->getUri();
		$query = QueryUtil::parse($uri->getQuery());

		$parameters = [
			'oauth_consumer_key'     => $this->options->key,
			'oauth_nonce'            => $this->nonce(),
			'oauth_signature_method' => 'HMAC-SHA1',
			'oauth_timestamp'        => time(),
			'oauth_token'            => $token->accessToken,
			'oauth_version'          => '1.0',
		];

		$parameters['oauth_signature'] = $this->getSignature(
			(string)$uri->withQuery('')->withFragment(''),
			array_merge($query, $parameters),
			$request->getMethod(),
			$token->accessTokenSecret
		);

		if(isset($query['oauth_session_handle'])){
			$parameters['oauth_session_handle'] = $query['oauth_session_handle']; // @codeCoverageIgnore
		}

		return $request->withHeader('Authorization', 'OAuth '.QueryUtil::build($parameters, null, ', ', '"'));
	}

}

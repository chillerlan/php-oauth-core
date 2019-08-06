<?php
/**
 * Class OAuth1Provider
 *
 * @link https://tools.ietf.org/html/rfc5849
 *
 * @filesource   OAuth1Provider.php
 * @created      09.07.2017
 * @package      chillerlan\OAuth\Core
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2017 Smiley
 * @license      MIT
 */

namespace chillerlan\OAuth\Core;

use DateTime;
use Psr\Http\Message\{RequestInterface, ResponseInterface, UriInterface};

use function array_merge, base64_encode, bin2hex, function_exists, hash_hmac,
	implode, in_array, is_array, parse_str, parse_url, random_bytes, strtoupper;
use function chillerlan\HTTP\Psr7\{build_http_query, decompress_content, merge_query, r_rawurlencode};

abstract class OAuth1Provider extends OAuthProvider implements OAuth1Interface{

	/**
	 * @var string
	 */
	protected $requestTokenURL;

	/**
	 * @inheritDoc
	 */
	public function getAuthURL(array $params = null):UriInterface{

		$params = array_merge(
			$params ?? [],
			['oauth_token' => $this->getRequestToken()->accessToken]
		);

		return $this->uriFactory->createUri(merge_query($this->authURL, $params));
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
			'oauth_timestamp'        => (new DateTime)->format('U'),
			'oauth_version'          => '1.0',
		];

		$params['oauth_signature'] = $this->getSignature($this->requestTokenURL, $params, 'POST');

		$request = $this->requestFactory
			->createRequest('POST', $this->requestTokenURL)
			->withHeader('Authorization', 'OAuth '.build_http_query($params, true, ', ', '"'))
			->withHeader('Accept-Encoding', 'identity')
		;

		foreach($this->authHeaders as $header => $value){
			$request = $request->withAddedHeader($header, $value);
		}

		return $this->parseTokenResponse($this->http->sendRequest($request), true);
	}

	/**
	 * @param \Psr\Http\Message\ResponseInterface $response
	 * @param bool|null                           $checkCallbackConfirmed
	 *
	 * @return \chillerlan\OAuth\Core\AccessToken
	 * @throws \chillerlan\OAuth\Core\ProviderException
	 */
	protected function parseTokenResponse(ResponseInterface $response, bool $checkCallbackConfirmed = null):AccessToken{
		parse_str(decompress_content($response), $data);

		if(!$data || !is_array($data)){
			throw new ProviderException('unable to parse token response');
		}
		elseif(isset($data['error'])){
			throw new ProviderException('error retrieving access token: '.$data['error']);
		}
		elseif(!isset($data['oauth_token']) || !isset($data['oauth_token_secret'])){
			throw new ProviderException('invalid token');
		}

		if($checkCallbackConfirmed && (!isset($data['oauth_callback_confirmed']) || $data['oauth_callback_confirmed'] !== 'true')){
			throw new ProviderException('oauth callback unconfirmed');
		}

		$token = new AccessToken([
			'provider'          => $this->serviceName,
			'accessToken'       => $data['oauth_token'],
			'accessTokenSecret' => $data['oauth_token_secret'],
			'expires'           => AccessToken::EOL_NEVER_EXPIRES,
		]);

		unset($data['oauth_token'], $data['oauth_token_secret']);

		$token->extraParams = $data;

		$this->storage->storeAccessToken($this->serviceName, $token);

		return $token;
	}

	/**
	 * returns a random 32 byte hex string
	 *
	 * @return string
	 */
	protected function nonce():string{
		$nonce = random_bytes(32);

		// use the sodium extension if available
		return function_exists('sodium_bin2hex')
			? \sodium_bin2hex($nonce)
			: bin2hex($nonce);
	}

	/**
	 * @param string $url
	 * @param array  $params
	 * @param string $method
	 * @param string $accessTokenSecret
	 *
	 * @return string
	 * @throws \chillerlan\OAuth\Core\ProviderException
	 */
	protected function getSignature(string $url, array $params, string $method, string $accessTokenSecret = null):string{
		$parseURL = parse_url($url);

		if(!isset($parseURL['host']) || !isset($parseURL['scheme']) || !in_array($parseURL['scheme'], ['http', 'https'], true)){
			throw new ProviderException('getSignature: invalid url');
		}

		parse_str($parseURL['query'] ?? '', $query);

		$signatureParams = array_merge($query, $params);

		if(isset($signatureParams['oauth_signature'])){
			unset($signatureParams['oauth_signature']);
		}

		$key  = implode('&', r_rawurlencode([$this->options->secret, $accessTokenSecret ?? '']));
		$data = r_rawurlencode([
			strtoupper($method ?? 'POST'),
			$parseURL['scheme'].'://'.$parseURL['host'].($parseURL['path'] ?? ''),
			build_http_query($signatureParams),
		]);

		return base64_encode(hash_hmac('sha1', implode('&', $data), $key, true));
	}

	/**
	 * @inheritDoc
	 */
	public function getAccessToken(string $token, string $verifier):AccessToken{
		$request = $this->requestFactory
			->createRequest('POST', merge_query($this->accessTokenURL, ['oauth_verifier' => $verifier]))
			->withHeader('Accept-Encoding', 'identity')
		;

		$request = $this->getRequestAuthorization($request, $this->storage->getAccessToken($this->serviceName));

		return $this->parseTokenResponse($this->http->sendRequest($request));
	}

	/**
	 * @inheritDoc
	 */
	public function getRequestAuthorization(RequestInterface $request, AccessToken $token):RequestInterface{
		$uri = $request->getUri();

		parse_str($uri->getQuery(), $query);

		$parameters = [
			'oauth_consumer_key'     => $this->options->key,
			'oauth_nonce'            => $this->nonce(),
			'oauth_signature_method' => 'HMAC-SHA1',
			'oauth_timestamp'        => (new DateTime)->format('U'),
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

		return $request->withHeader('Authorization', 'OAuth '.build_http_query($parameters, true, ', ', '"'));
	}

}

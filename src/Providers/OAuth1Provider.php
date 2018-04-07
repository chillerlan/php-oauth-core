<?php
/**
 * Class OAuth1Provider
 *
 * @filesource   OAuth1Provider.php
 * @created      09.07.2017
 * @package      chillerlan\OAuth\Providers
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2017 Smiley
 * @license      MIT
 */

namespace chillerlan\OAuth\Providers;

use chillerlan\OAuth\Token;
use chillerlan\HTTP\HTTPResponseInterface;
use DateTime;

abstract class OAuth1Provider extends OAuthProvider implements OAuth1Interface{

	/**
	 * @var string
	 */
	protected $requestTokenURL;

	/**
	 * @var string
	 */
	protected $tokenSecret;

	/**
	 * @param array $params
	 *
	 * @return string
	 */
	public function getAuthURL(array $params = null):string {

		$params = array_merge(
			$params ?? [],
			['oauth_token' => $this->getRequestToken()->requestToken]
		);

		return $this->authURL.'?'.$this->httpBuildQuery($params);
	}

	/**
	 * @return \chillerlan\OAuth\Token
	 */
	public function getRequestToken():Token {
		$params   = $this->getRequestTokenHeaderParams();

		return $this->parseTokenResponse(
			$this->httpPOST(
				$this->requestTokenURL,
				[],
				null,
				array_merge($this->authHeaders, [
					'Authorization' => 'OAuth '.$this->httpBuildQuery($params, true, ', ', '"')
				])
			),
			true
		);
	}

	/**
	 * @param \chillerlan\HTTP\HTTPResponseInterface $response
	 * @param bool|null                              $checkCallbackConfirmed
	 *
	 * @return \chillerlan\OAuth\Token
	 * @throws \chillerlan\OAuth\Providers\ProviderException
	 */
	protected function parseTokenResponse(HTTPResponseInterface $response, bool $checkCallbackConfirmed = null):Token {
		parse_str($response->body, $data);

		if(!$data || !is_array($data)){
			throw new ProviderException('unable to parse token response');
		}
		elseif(isset($data['error'])){
			throw new ProviderException('error retrieving access token: '.$data['error']);
		}
		elseif(!isset($data['oauth_token']) || !isset($data['oauth_token_secret'])){
			throw new ProviderException('token missing');
		}

		if(($checkCallbackConfirmed ?? false)
		   && (!isset($data['oauth_callback_confirmed']) || $data['oauth_callback_confirmed'] !== 'true')
		){
			throw new ProviderException('oauth callback unconfirmed');
		}

		$token = new Token([
			'provider'           => $this->serviceName,
			'requestToken'       => $data['oauth_token'],
			'requestTokenSecret' => $data['oauth_token_secret'],
			'accessToken'        => $data['oauth_token'],
			'accessTokenSecret'  => $data['oauth_token_secret'],
			'expires'            => Token::EOL_NEVER_EXPIRES,
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
	protected function nonce():string {
		$nonce = random_bytes(32);

		// use the sodium extension if available
		return function_exists('sodium_bin2hex') ? sodium_bin2hex($nonce) : bin2hex($nonce);
	}

	/**
	 * @return array
	 */
	protected function getRequestTokenHeaderParams():array {
		$params = [
			'oauth_callback'         => $this->options->callbackURL,
			'oauth_consumer_key'     => $this->options->key,
			'oauth_nonce'            => $this->nonce(),
			'oauth_signature_method' => 'HMAC-SHA1',
			'oauth_timestamp'        => (new DateTime())->format('U'),
			'oauth_version'          => '1.0',
		];

		$params['oauth_signature'] = $this->getSignature($this->requestTokenURL, $params);

		return $params;
	}

	/**
	 * @param string $url
	 * @param array  $params
	 * @param string $method
	 *
	 * @return string
	 * @throws \chillerlan\OAuth\Providers\ProviderException
	 */
	public function getSignature(string $url, array $params, string $method = null):string {
		$parseURL = parse_url($url);

		if(!isset($parseURL['host']) || !isset($parseURL['scheme']) || !in_array($parseURL['scheme'], ['http', 'https'], true)){
			throw new ProviderException('getSignature: invalid url');
		}

		parse_str($parseURL['query'] ?? '', $query);

		$data = $this->getSignatureData(
			$parseURL['scheme'].'://'.$parseURL['host'].($parseURL['path'] ?? ''),
			array_merge($query, $params),
			$method ?? 'POST'
		);

		$key = implode('&', $this->rawurlencode([$this->options->secret, $this->tokenSecret ?? '']));

		return base64_encode(hash_hmac('sha1', $data, $key, true));
	}

	/**
	 * @param string $method
	 * @param string $signatureURL
	 * @param array  $signatureParams
	 *
	 * @return string
	 */
	protected function getSignatureData(string $signatureURL, array $signatureParams, string $method){

		if(isset($signatureParams['oauth_signature'])){
			unset($signatureParams['oauth_signature']);
		}

		$data = [
			strtoupper($method),
			$signatureURL,
			$this->httpBuildQuery($signatureParams),
		];

		return implode('&', $this->rawurlencode($data));
	}

	/**
	 * @param string      $token
	 * @param string      $verifier
	 * @param string|null $tokenSecret
	 *
	 * @return \chillerlan\OAuth\Token
	 */
	public function getAccessToken(string $token, string $verifier, string $tokenSecret = null):Token {
		$this->tokenSecret = $tokenSecret;

		if(empty($this->tokenSecret)){
			$this->tokenSecret = $this->storage->getAccessToken($this->serviceName)->requestTokenSecret;
		}

		$body = ['oauth_verifier' => $verifier];

		return $this->parseTokenResponse(
			$this->httpPOST($this->accessTokenURL, [], $body, $this->getAccessTokenHeaders($body))
		);
	}

	/**
	 * @param array $body
	 *
	 * @return array
	 */
	protected function getAccessTokenHeaders(array $body):array {
		return $this->requestHeaders($this->accessTokenURL, $body, 'POST', [], $this->storage->getAccessToken($this->serviceName));
	}

	/**
	 * @param string                  $url
	 * @param array|string            $params
	 * @param string                  $method
	 * @param array                   $headers
	 * @param \chillerlan\OAuth\Token $token
	 *
	 * @return array
	 * @throws \Exception
	 */
	protected function requestHeaders(string $url, $params = null, string $method, array $headers = null, Token $token):array{
		$this->tokenSecret = $token->accessTokenSecret;
		$parameters        = $this->requestHeaderParams($token);

		$parameters['oauth_signature'] = $this->getSignature($url, array_merge($params ?? [], $parameters), $method);

		if(isset($params['oauth_session_handle'])){
			$parameters['oauth_session_handle'] = $params['oauth_session_handle'];
			unset($params['oauth_session_handle']);
		}

		return array_merge($headers ?? [], $this->apiHeaders, [
			'Authorization' => 'OAuth '.$this->httpBuildQuery($parameters, true, ', ', '"')
		]);
	}

	/**
	 * @param \chillerlan\OAuth\Token $token
	 *
	 * @return array
	 * @throws \Exception
	 */
	protected function requestHeaderParams(Token $token):array {
		return [
			'oauth_consumer_key'     => $this->options->key,
			'oauth_nonce'            => $this->nonce(),
			'oauth_signature_method' => 'HMAC-SHA1',
			'oauth_timestamp'        => (new DateTime())->format('U'),
			'oauth_token'            => $token->accessToken,
			'oauth_version'          => '1.0',
		];
	}

	/**
	 * @param string $path
	 * @param array  $params
	 * @param string $method
	 * @param null   $body
	 * @param array  $headers
	 *
	 * @return \chillerlan\HTTP\HTTPResponseInterface
	 */
	public function request(string $path, array $params = null, string $method = null, $body = null, array $headers = null):HTTPResponseInterface{
		$method = $method ?? 'GET';

		$headers = $this->requestHeaders(
			$this->apiURL.$path,
			$body ?? $params,
			$method,
			$headers,
			$this->storage->getAccessToken($this->serviceName)
		);

		return $this->httpRequest($this->apiURL.$path, $params, $method, $body, $headers);
	}

}

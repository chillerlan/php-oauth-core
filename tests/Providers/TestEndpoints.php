<?php
/**
 * Class TestEndpoints
 *
 * @created      09.09.2018
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2018 smiley
 * @license      MIT
 */

namespace chillerlan\OAuthTest\Providers;

use chillerlan\OAuth\MagicAPI\EndpointMap;

final class TestEndpoints extends EndpointMap{

	protected string $API_BASE = '/api';

	protected array $test = [
		'path'          => '/test/%1$s/%2$s'.OAuthProviderTestAbstract::ECHO_REQUEST,
		'method'        => 'GET',
		'query'         => ['foo'],
		'path_elements' => ['a', 'b'],
		'body'          => null,
		'headers'       => null,
	];

	protected array $postNoPathNoQuery = [
		'path'          => '/post/nopathnoquery'.OAuthProviderTestAbstract::ECHO_REQUEST,
		'method'        => 'POST',
		'query'         => null,
		'path_elements' => null,
		'body'          => ['foo'],
		'headers'       => ['content-type' => 'application/json'],
	];

	protected array $postWithPathNoQuery = [
		'path'          => '/post/%1$s'.OAuthProviderTestAbstract::ECHO_REQUEST,
		'method'        => 'POST',
		'query'         => null,
		'path_elements' => ['path'],
		'body'          => ['foo'],
		'headers'       => ['content-type' => 'application/json'],
	];

	protected array $postNoPathWithQuery = [
		'path'          => '/post/nopathwithquery'.OAuthProviderTestAbstract::ECHO_REQUEST,
		'method'        => 'POST',
		'query'         => ['query'],
		'path_elements' => null,
		'body'          => ['foo'],
		'headers'       => ['content-type' => 'application/json'],
	];

	protected array $postWithPathAndQuery = [
		'path'          => '/post/%1$s'.OAuthProviderTestAbstract::ECHO_REQUEST,
		'method'        => 'POST',
		'query'         => ['query'],
		'path_elements' => ['pathandquery'],
		'body'          => ['foo'],
		'headers'       => ['content-type' => 'application/json'],
	];

}

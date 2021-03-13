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

use chillerlan\HTTP\MagicAPI\EndpointMap;

class TestEndpoints extends EndpointMap{

	protected array $test = [
		'path'          => '/test/%1$s',
		'method'        => 'GET',
		'query'         => ['foo'],
		'path_elements' => ['id'],
		'body'          => null,
		'headers'       => [],
	];

}

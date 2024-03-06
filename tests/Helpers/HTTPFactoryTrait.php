<?php
/**
 * HTTPFactoryTrait.php
 *
 * @created      06.03.2024
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2024 smiley
 * @license      MIT
 */

namespace chillerlan\OAuthTest\Helpers;

use Psr\Http\Message\{RequestFactoryInterface, ResponseFactoryInterface, StreamFactoryInterface, UriFactoryInterface};
use Exception;
use function class_exists, constant, defined, sprintf;

/**
 * Trait HTTPFactoryTrait
 */
trait HTTPFactoryTrait{

	private array $FACTORIES = [
		'requestFactory'  => 'REQUEST_FACTORY',
		'responseFactory' => 'RESPONSE_FACTORY',
		'streamFactory'   => 'STREAM_FACTORY',
		'uriFactory'      => 'URI_FACTORY',
	];

	protected RequestFactoryInterface  $requestFactory;
	protected ResponseFactoryInterface $responseFactory;
	protected StreamFactoryInterface   $streamFactory;
	protected UriFactoryInterface      $uriFactory;

	/**
	 * @throws \Exception
	 */
	protected function initFactories():void{

		foreach($this->FACTORIES as $property => $const){

			if(!defined($const)){
				throw new Exception(sprintf('constant "%s" not defined -> see phpunit.xml', $const));
			}

			$class = constant($const);

			if(!class_exists($class)){
				throw new Exception(sprintf('invalid class: "%s"', $class));
			}

			$this->{$property} = new $class;
		}

	}

}

<?php
/**
 * Class EndpointDocblock
 *
 * @created      08.09.2018
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2018 smiley
 * @license      MIT
 */

namespace chillerlan\OAuthTest\MagicAPI;

use chillerlan\OAuth\Core\OAuthInterface;
use chillerlan\OAuth\MagicAPI\EndpointMapInterface;
use ReflectionClass;

use function count, dirname, file_get_contents, file_put_contents, implode, in_array, is_array, ksort, str_replace;

use const JSON_PRETTY_PRINT, PHP_EOL;

class EndpointDocblock{

	protected OAuthInterface $provider;

	protected EndpointMapInterface $endpointMap;

	/**
	 * EndpointDocblock constructor.
	 *
	 * @param \chillerlan\OAuth\Core\OAuthInterface           $provider
	 * @param \chillerlan\OAuth\MagicAPI\EndpointMapInterface $endpointMap
	 */
	public function __construct(OAuthInterface $provider, EndpointMapInterface $endpointMap){
		$this->provider    = $provider;
		$this->endpointMap = $endpointMap;
	}

	/**
	 * @param string $returntype
	 *
	 * @return string
	 */
	public function create(string $returntype):string{
		$n   = PHP_EOL;
		$str = '/**'.$n;
		$ep  = $this->endpointMap->toArray();

		ksort($ep);

		foreach($ep as $methodName => $params){

			if($methodName === 'API_BASE'){
				continue;
			}

			$args = [];

			if(isset($params['path_elements']) && count($params['path_elements']) > 0){

				foreach($params['path_elements'] as $i){
					$args[] = 'string $'.$i;
				}

			}

			if(isset($params['query']) && !empty($params['query'])){
				$args[] = 'array $params = [\''.implode('\', \'', $params['query']).'\']';
			}

			if(isset($params['method']) && in_array($params['method'], ['PATCH', 'POST', 'PUT', 'DELETE'], true)){

				if(isset($params['body']) && $params['body'] !== null){
					$args[] = is_array($params['body']) ? 'array $body = [\''.implode('\', \'', $params['body']).'\']' : 'array $body = []';
				}

			}

			$str .= ' * @method \\'.$returntype.' '.$methodName.'('.implode(', ', $args).')'.$n;
		}

		$str .= ' *'.'/'.$n;

		$reflection = new ReflectionClass($this->provider);
		$classfile  = $reflection->getFileName();

		file_put_contents($classfile, str_replace($reflection->getDocComment().$n, $str, file_get_contents($classfile)));

		return $str;
	}

	/**
	 * @param string      $name
	 * @param string      $returntype
	 * @param string|null $namespace
	 *
	 * @return bool
	 */
	public function createInterface(string $name, string $returntype, string $namespace = null):bool{
		$reflection    = new ReflectionClass($this->provider);
		$interfaceName = $name.'Interface';
		$n             = PHP_EOL;
		$ep            = $this->endpointMap->toArray();

		$str = '<?php'.$n.$n
		       .'namespace '.($namespace ?? $reflection->getNamespaceName()).';'.$n.$n
		       .'use '.$returntype.';'.$n.$n
		       .'interface '.$interfaceName.'{'.$n.$n;

		ksort($ep);

		foreach($ep as $methodName => $params){

			if($methodName === 'API_BASE'){
				continue;
			}

			$args = [];
			$str  .= "\t".'/**'.$n;

			if(isset($params['path_elements']) && is_array($params['path_elements']) && count($params['path_elements']) > 0){

				foreach($params['path_elements'] as $i){
					$a   = 'string $'.$i;
					$str .= "\t".' * @param '.$a.$n;

					$args[] = $a;
				}

			}

			if(!empty($params['query'])){
				$a      = 'array $params = [\''.implode('\', \'', $params['query']).'\']';
				$str    .= "\t".' * @param '.$a.$n;
				$args[] = $a;
			}

			if(isset($params['method']) && in_array($params['method'], ['PATCH', 'POST', 'PUT', 'DELETE'])){

				if($params['body'] !== null){
					$a      = is_array($params['body']) ? 'array $body = [\''.implode('\', \'', $params['body']).'\']' : 'array $body = []';
					$str    .= "\t".' * @param '.$a.$n;
					$args[] = $a;
				}

			}

			$r = new ReflectionClass($returntype);

			$str .= "\t".' * @return \\'.$r->getName().$n;
			$str .= "\t".' */'.$n;
			$str .= "\t".'public function '.$methodName.'('.implode(', ', $args).'):'.$r->getShortName().';'.$n.$n;
		}

		$str .= '}'.$n;

		$file = dirname($reflection->getFileName()).'/'.$interfaceName.'.php';

		return (bool)file_put_contents($file, $str);
	}

	/**
	 * @param string|null $name
	 * @param int         $jsonOptions
	 *
	 * @return bool
	 */
	public function createJSON(string $name = null, int $jsonOptions = JSON_PRETTY_PRINT):bool{
		$reflection = new ReflectionClass($this->endpointMap);

		$file = dirname($reflection->getFileName()).'/'.($name ?? $reflection->getShortName()).'.json';

		return (bool)file_put_contents($file, $this->endpointMap->toJSON($jsonOptions));
	}

}

<?php
/**
 * Class OAuthOptions
 *
 * @filesource   OAuthOptions.php
 * @created      09.07.2017
 * @package      chillerlan\OAuth
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2017 Smiley
 * @license      MIT
 */

namespace chillerlan\OAuth;

use chillerlan\HTTP\HTTPOptionsTrait;
use chillerlan\Traits\{
	Container, ContainerInterface, Crypto\MemzeroDestructorTrait
};

/**
 * OAuthOptionsTrait
 *
 * @property string     $key
 * @property string     $secret
 * @property string     $callbackURL
 * @property bool       $sandboxMode
 * @property bool       $sessionStart
 * @property string     $sessionTokenVar
 * @property string     $sessionStateVar
 * @property bool       $useEncryption
 * @property string     $storageCryptoKey
 * @property bool       $tokenAutoRefresh
 *
 * @property string     $dbLabelHashAlgo
 * @property string     $dbLabelFormat
 * @property string|int $dbUserID
 *
 * @property string     $dbTokenTable
 * @property string     $dbTokenTableExpires
 * @property string     $dbTokenTableLabel
 * @property string     $dbTokenTableProviderID
 * @property string     $dbTokenTableState
 * @property string     $dbTokenTableToken
 * @property string     $dbTokenTableUser
 *
 * @property string     $dbProviderTable
 * @property string     $dbProviderTableID
 * @property string     $dbProviderTableName
 *
 * HTTPOptionsTrait
 *
 * @property string     $user_agent
 * @property int        $timeout
 * @property array      $curl_options
 * @property string     $ca_info
 * @property int        $max_redirects
 */
class OAuthOptions implements ContainerInterface{
	use OAuthOptionsTrait, HTTPOptionsTrait, MemzeroDestructorTrait, Container{
		__construct as protected containerConstruct;
	}

	/**
	 * OAuthOptions constructor.
	 *
	 * @param array|null $properties
	 */
	public function __construct(array $properties = null){
		// enable encryption by default if possible...
		$this->useEncryption = extension_loaded('sodium');

		// ... then load and override the settings
		$this->containerConstruct($properties);
	}

}

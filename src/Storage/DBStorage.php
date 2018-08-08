<?php
/**
 * Class DBStorage
 *
 * @filesource   DBStorage.php
 * @created      16.07.2017
 * @package      chillerlan\OAuth\Storage
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2017 Smiley
 * @license      MIT
 */

namespace chillerlan\OAuth\Storage;

use chillerlan\Database\Database;
use chillerlan\OAuth\Core\AccessToken;
use chillerlan\Traits\ImmutableSettingsInterface;

class DBStorage extends OAuthStorageAbstract{

	/**
	 * @var \chillerlan\Database\Database
	 */
	protected $db;

	/**
	 * DBStorage constructor.
	 *
	 * @param \chillerlan\Traits\ImmutableSettingsInterface $options
	 * @param \chillerlan\Database\Database         $db
	 *
	 * @throws \chillerlan\OAuth\Storage\OAuthStorageException
	 */
	public function __construct(ImmutableSettingsInterface $options, Database $db){
		parent::__construct($options);

		if(!$this->options->dbTokenTable || !$this->options->dbProviderTable){
			throw new OAuthStorageException('invalid table config');
		}

		$this->db = $db->connect();
	}

	/**
	 * @return array
	 */
	protected function getProviders():array {
		return $this->db->select
			->cached()
			->from([$this->options->dbProviderTable])
			->query($this->options->dbProviderTableName)
			->__toArray();
	}

	/**
	 * @param string                             $service
	 * @param \chillerlan\OAuth\Core\AccessToken $token
	 *
	 * @return \chillerlan\OAuth\Storage\OAuthStorageInterface
	 * @throws \chillerlan\OAuth\Storage\OAuthStorageException
	 */
	public function storeAccessToken(string $service, AccessToken $token):OAuthStorageInterface{
		$providers = $this->getProviders();

		if(empty($providers) || !isset($providers[$service])){
			throw new OAuthStorageException('unknown service');
		}

		$values = [
			$this->options->dbTokenTableUser       => $this->options->dbUserID,
			$this->options->dbTokenTableProviderID => $providers[$service][$this->options->dbProviderTableID],
			$this->options->dbTokenTableToken      => $this->toStorage($token),
			$this->options->dbTokenTableExpires    => $token->expires,
		];

		if($this->hasAccessToken($service) === true){
			$this->db->update
				->table($this->options->dbTokenTable)
				->set($values)
				->where($this->options->dbTokenTableLabel, $this->getLabel($service))
				->query();

			return $this;
		}

		$values[$this->options->dbTokenTableLabel] = $this->getLabel($service);

		$this->db->insert
			->into($this->options->dbTokenTable)
			->values($values)
			->query();

		return $this;
	}

	/**
	 * @param string $service
	 *
	 * @return \chillerlan\OAuth\Core\AccessToken
	 * @throws \chillerlan\OAuth\Storage\OAuthStorageException
	 */
	public function getAccessToken(string $service):AccessToken{

		$r = $this->db->select
			->cols([$this->options->dbTokenTableToken])
			->from([$this->options->dbTokenTable])
			->where($this->options->dbTokenTableLabel, $this->getLabel($service))
			->query();

		if(is_bool($r) || $r->length < 1){
			throw new OAuthStorageException('token not found');
		}

		return $this->fromStorage($r[0]->{$this->options->dbTokenTableToken});
	}

	/**
	 * @param string $service
	 *
	 * @return bool
	 */
	public function hasAccessToken(string $service):bool{

		return (bool)$this->db->select
			->cols([$this->options->dbTokenTableToken])
			->from([$this->options->dbTokenTable])
			->where($this->options->dbTokenTableLabel, $this->getLabel($service))
			->count();
	}

	/**
	 * @param string $service
	 *
	 * @return \chillerlan\OAuth\Storage\OAuthStorageInterface
	 */
	public function clearAccessToken(string $service):OAuthStorageInterface{

		$this->db->delete
			->from($this->options->dbTokenTable)
			->where($this->options->dbTokenTableLabel, $this->getLabel($service))
			->query();

		return $this;
	}

	/**
	 * @return \chillerlan\OAuth\Storage\OAuthStorageInterface
	 */
	public function clearAllAccessTokens():OAuthStorageInterface{

		$this->db->delete
			->from($this->options->dbTokenTable)
			->where($this->options->dbTokenTableUser, $this->options->dbUserID)
			->query();

		return $this;
	}

	/**
	 * @param string $service
	 * @param string $state
	 *
	 * @return \chillerlan\OAuth\Storage\OAuthStorageInterface
	 */
	public function storeCSRFState(string $service, string $state):OAuthStorageInterface{

		$this->db->update
			->table($this->options->dbTokenTable)
			->set([$this->options->dbTokenTableState => $state])
			->where($this->options->dbTokenTableLabel, $this->getLabel($service))
			->query();

		return $this;
	}

	/**
	 * @param string $service
	 *
	 * @return string
	 * @throws \chillerlan\OAuth\Storage\OAuthStorageException
	 */
	public function getCSRFState(string $service):string{

		$r = $this->db->select
			->cols([$this->options->dbTokenTableState])
			->from([$this->options->dbTokenTable])
			->where($this->options->dbTokenTableLabel, $this->getLabel($service))
			->query();

		if(is_bool($r) || $r->length < 1){
			throw new OAuthStorageException('state not found');
		}

		return trim($r[0]->state);
	}

	/**
	 * @param string $service
	 *
	 * @return bool
	 */
	public function hasCSRFState(string $service):bool{

		$r = $this->db->select
			->cols([$this->options->dbTokenTableState])
			->from([$this->options->dbTokenTable])
			->where($this->options->dbTokenTableLabel, $this->getLabel($service))
			->query();

		if(is_bool($r) || $r->length < 1 || empty(trim($r[0]->state))){
			return false;
		}

		return true;
	}

	/**
	 * @param string $service
	 *
	 * @return \chillerlan\OAuth\Storage\OAuthStorageInterface
	 */
	public function clearCSRFState(string $service):OAuthStorageInterface{

		$this->db->update
			->table($this->options->dbTokenTable)
			->set([$this->options->dbTokenTableState => null])
			->where($this->options->dbTokenTableLabel, $this->getLabel($service))
			->query();

		return $this;
	}

	/**
	 * @return \chillerlan\OAuth\Storage\OAuthStorageInterface
	 */
	public function clearAllCSRFStates():OAuthStorageInterface{

		$this->db->update
			->table($this->options->dbTokenTable)
			->set([$this->options->dbTokenTableState => null])
			->where($this->options->dbTokenTableUser, $this->options->dbUserID)
			->query();

		return $this;
	}

	/**
	 * @param string $service
	 *
	 * @return string
	 */
	protected function getLabel(string $service):string{
		return hash($this->options->dbLabelHashAlgo, sprintf($this->options->dbLabelFormat, $this->options->dbUserID, $service));
	}

}

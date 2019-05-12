<?php
/**
 * Class OAuthTestLogger
 *
 * @filesource   OAuthTestLogger.php
 * @created      04.05.2019
 * @package      chillerlan\OAuthTest
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2019 smiley
 * @license      MIT
 */

namespace chillerlan\OAuthTest;

use Psr\Log\AbstractLogger;
use Psr\Log\LogLevel;

class OAuthTestLogger extends AbstractLogger{

	protected const E_DEBUG     = 0x01;
	protected const E_INFO      = 0x02;
	protected const E_NOTICE    = 0x04;
	protected const E_WARNING   = 0x08;
	protected const E_ERROR     = 0x10;
	protected const E_CRITICAL  = 0x20;
	protected const E_ALERT     = 0x40;
	protected const E_EMERGENCY = 0x80;

	protected const LEVELS = [
		LogLevel::DEBUG     => self::E_DEBUG,
		LogLevel::INFO      => self::E_INFO,
		LogLevel::NOTICE    => self::E_NOTICE,
		LogLevel::WARNING   => self::E_WARNING,
		LogLevel::ERROR     => self::E_ERROR,
		LogLevel::CRITICAL  => self::E_CRITICAL,
		LogLevel::ALERT     => self::E_ALERT,
		LogLevel::EMERGENCY => self::E_EMERGENCY,
	];

	/**
	 * @see \Psr\Log\LogLevel
	 * @var string
	 */
	protected $minLoglevel;

	/**
	 * OAuthTestLogger constructor.
	 *
	 * @param string $minLoglevel
	 */
	public function __construct(string $minLoglevel = null){
		$this->minLoglevel = $minLoglevel;
	}

	/**
	 * @param string $level
	 * @param string $message
	 * @param array  $context
	 */
	public function log($level, $message, array $context = []){

		if(!isset($this::LEVELS[$level]) || !isset($this::LEVELS[$this->minLoglevel])){
			return;
		}

		if($this::LEVELS[$level] >= $this::LEVELS[$this->minLoglevel]){
			echo sprintf(
				     '[%s][%s] %s',
				     date('Y-m-d H:i:s'),
				     substr($level, 0, 4),
				     str_replace("\n", "\n".str_repeat(' ', 28), trim($message))
			     )."\n";
		}

	}
}

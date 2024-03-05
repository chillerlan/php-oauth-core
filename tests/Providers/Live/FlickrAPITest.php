<?php
/**
 * Class FlickrAPITest
 *
 * @created      15.07.2017
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2017 Smiley
 * @license      MIT
 */

namespace chillerlan\OAuthTest\Providers\Live;

use chillerlan\HTTP\Utils\MessageUtil;
use chillerlan\OAuth\Core\ProviderException;
use chillerlan\OAuth\Providers\Flickr;
use chillerlan\OAuthTest\Providers\OAuth1APITestAbstract;

/**
 * @property  \chillerlan\OAuth\Providers\Flickr $provider
 */
class FlickrAPITest extends OAuth1APITestAbstract{

	protected string $FQN = Flickr::class;
	protected string $ENV = 'FLICKR';

	protected string $test_name;
	protected string $test_id;

	protected function setUp():void{
		parent::setUp();

		$tokenParams     = $this->storage->getAccessToken($this->provider->serviceName)->extraParams;

		$this->test_name = $tokenParams['username'];
		$this->test_id   = $tokenParams['user_nsid'];
	}

	public function testMe():void{
		try{
			$j = MessageUtil::decodeJSON($this->provider->me());

			$this::assertSame($this->test_name, $j->user->username->_content);
			$this::assertSame($this->test_id, $j->user->id);
		}
		catch(ProviderException){
			$this::markTestSkipped('token is missing or expired');
		}

	}

}

<?php
/**
 * Class MusicBrainzTest
 *
 * @created      31.07.2018
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2017 Smiley
 * @license      MIT
 */

namespace chillerlan\OAuthTest\Providers\Live;

use chillerlan\HTTP\Utils\MessageUtil;
use chillerlan\OAuth\Providers\MusicBrainz;
use chillerlan\OAuth\Providers\ProviderException;
use chillerlan\OAuthTest\Providers\OAuth2APITestAbstract;

class MusicBrainzAPITest extends OAuth2APITestAbstract{

	protected string $ENV = 'MUSICBRAINZ';

	protected function getProviderFQCN():string{
		return MusicBrainz::class;
	}

	public function testArtistId():void{
		try{
			$r = $this->provider->request('/artist/573510d6-bb5d-4d07-b0aa-ea6afe39e28d', ['inc' => 'url-rels work-rels']);
			$j = MessageUtil::decodeJSON($r);

			$this::assertSame('Helium', $j->name);
			$this::assertSame('573510d6-bb5d-4d07-b0aa-ea6afe39e28d', $j->id);
		}
		catch(ProviderException){
			$this::markTestSkipped('token is missing or expired');
		}
	}

	public function testArtistIdXML():void{
		try{
			$r = $this->provider->request('/artist/573510d6-bb5d-4d07-b0aa-ea6afe39e28d', ['inc' => 'url-rels work-rels', 'fmt' => 'xml']);
			$x = MessageUtil::decodeXML($r);

			$this::assertSame('Helium', (string)$x->artist[0]->name);
			$this::assertSame('573510d6-bb5d-4d07-b0aa-ea6afe39e28d', (string)$x->artist[0]->attributes()['id']);
		}
		catch(ProviderException){
			$this::markTestSkipped('token is missing or expired');
		}
	}


}

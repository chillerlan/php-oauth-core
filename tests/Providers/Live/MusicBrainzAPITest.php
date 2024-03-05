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
use chillerlan\OAuthTest\Providers\OAuth2APITestAbstract;

class MusicBrainzAPITest extends OAuth2APITestAbstract{

	protected string $FQN         = MusicBrainz::class;
	protected string $ENV         = 'MUSICBRAINZ';

	public function testArtistId():void{
		$r = $this->provider->request('/artist/573510d6-bb5d-4d07-b0aa-ea6afe39e28d', ['inc' => 'url-rels work-rels']);
		$j = MessageUtil::decodeJSON($r);

		$this::assertSame('Helium', $j->name);
		$this::assertSame('573510d6-bb5d-4d07-b0aa-ea6afe39e28d', $j->id);
	}

	public function testArtistIdXML():void{
		$r = $this->provider->request('/artist/573510d6-bb5d-4d07-b0aa-ea6afe39e28d', ['inc' => 'url-rels work-rels', 'fmt' => 'xml']);
		$x = MessageUtil::decodeXML($r);

		$this::assertSame('Helium', (string)$x->artist[0]->name);
		$this::assertSame('573510d6-bb5d-4d07-b0aa-ea6afe39e28d', (string)$x->artist[0]->attributes()['id']);
	}


}

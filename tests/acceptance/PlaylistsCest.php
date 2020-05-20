<?php 

class PlaylistsCest
{
    public function _before(AcceptanceTester $I)
    {
    }

    public function tryPlaylistsPage(AcceptanceTester $I)
    {
    	$I->login($I);
		$I->amOnPage('/playlists');
		$I->see('Playlists');
	}

    public function tryPlaylist(AcceptanceTester $I)
    {
        $I->login($I);
        $I->amOnPage('/playlists');
        $I->click('play');
        $I->wait(1);
        $I->seeElement('audio');
    }

}

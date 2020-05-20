<?php 

class SongsCest
{
    public function _before(AcceptanceTester $I)
    {
    }

    public function trySongsPage(AcceptanceTester $I)
    {
    	$I->login($I);
		$I->amOnPage('/songs');
		$I->see('Current Songs');
	}

    public function tryViewSongEditPage(AcceptanceTester $I)
    {
    	$I->login($I);
		$I->amOnPage('/songs');
		$I->click('edit');
		$I->see('Composer');
	}

    public function tryPlayAlbumSongs(AcceptanceTester $I)
    {
        $I->login($I);
        $I->amOnPage('/songs');
        $I->click('play album');
        $I->wait(1);
        $I->seeElement('audio');
    }

    public function tryAddToPlaylist(AcceptanceTester $I)
    {
        $I->login($I);
        $I->amOnPage('/songs');
        $I->click('add to playlist');
        $I->wait(1);
        $I->see('Add to Existing Playlist');
    }
}

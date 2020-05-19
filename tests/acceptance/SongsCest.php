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
}

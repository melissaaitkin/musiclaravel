<?php 

class ArtistsCest
{
    public function _before(AcceptanceTester $I)
    {
    }

    public function tryArtistsPage(AcceptanceTester $I)
    {
        $I->login($I);
        $I->amOnPage('/artists');
        $I->see('Current Artists');
	}

    public function tryViewArtistEditPage(AcceptanceTester $I)
    {
        $I->login($I);
        $I->amOnPage('/artists');
        $I->click('edit');
        $I->see('Country');
	}

}

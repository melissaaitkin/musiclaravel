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

    public function tryViewEditArtistPage(AcceptanceTester $I)
    {
        $I->login($I);
        $I->amOnPage('/artists');
        $I->see('Current Artists');
        $I->click('edit');
        $I->see('Country');
    }

    public function tryPlayArtistsSongs(AcceptanceTester $I)
    {
        $I->login($I);
        $I->amOnPage('/artists');
        $I->click('play songs');
        $I->wait(1);
        $I->seeElement('audio');
    }

}
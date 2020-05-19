<?php 

class ArtistsCest
{
    public function _before(AcceptanceTester $I)
    {
    }

    public function tryArtistsPage(AcceptanceTester $I)
    {
    	$I->login($I, $_ENV['TEST_EMAIL'], $_ENV['TEST_PW']);
		$I->amOnPage('/artists');
		$I->see('Current Artists');
	}
}

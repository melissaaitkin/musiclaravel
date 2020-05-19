<?php 

class LoginCest
{
    public function _before(AcceptanceTester $I)
    {
    }

    public function tryRegister(AcceptanceTester $I)
    {
        $I->amOnPage('/');
        $I->click('Register');
        $I->fillField('Name', $_ENV['TEST_NAME']);
        $I->fillField('E-Mail Address', $_ENV['TEST_EMAIL']);
        $I->fillField('Password', $_ENV['TEST_PW']);
        $I->fillField('Confirm Password', $_ENV['TEST_PW']);
        $I->click('Register', '.btn-primary');
    }

    public function tryLogin(AcceptanceTester $I)
    {
        $I->amOnPage('/');
        $I->click('Login');
        $I->fillField('E-Mail Address', $_ENV['TEST_EMAIL']);
        $I->fillField('Password', $_ENV['TEST_PW']);
        $I->click('Login', '.btn-primary');
        $I->see($_ENV['TEST_NAME']);
        $I->see('Current Songs');
    }

}

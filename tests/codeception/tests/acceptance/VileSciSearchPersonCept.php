<?php 

$I = new AcceptanceTester($scenario);
$I->wantTo('Test the Search-Feature of VileSci');
$I->amOnPage('/vilesci/personen/suche.php');
$I->lookForwardTo('Personensuche');
$I->seeElement('input[name="searchstr"]');
$I->seeElement('input[type=submit][value=Suchen]');
$I->fillField('searchstr', 'Vicenta');
$I->click('Suchen');
$I->see('McKenzie');
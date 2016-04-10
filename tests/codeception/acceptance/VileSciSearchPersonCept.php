<?php 
$I = new AcceptanceTester($scenario);
$I->wantTo('test the Search-Feature of VileSci');

$I->wantTo('test the left nav-frame');
$I->amOnPage('/vilesci/left.php?categorie=Personen');
$I->seeElement('.left_nav');
$I->click('a');

$I->wantTo('test the searchPerson Page');
$I->amOnPage('/vilesci/personen/suche.php');
$I->seeElement('[name=search]');
$I->fillField('searchstr', 'Vicenta');
$I->click('[type=submit]');
$I->see('McKenzie');
?>

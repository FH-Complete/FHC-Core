<?php 
$I = new AcceptanceTester($scenario);
$I->wantTo('CIS Startseite Testen');
$I->amOnPage('/cis/index.html');
$I->see('Powered by FH Complete');
?>

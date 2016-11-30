<?php

$I = new AcceptanceTester($scenario);
$I->wantTo('CIS start page');
$I->amOnPage('/cis/index.html');
$I->See('Powered by FH Complete');

?>
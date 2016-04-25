<?php 
$I = new AcceptanceTester($scenario);
$I->wantTo('test the Startpage of VileSci');

$I->wantTo('test this over vilesci/index.php');
$I->amOnPage('/vilesci/index.php');
$I->See('This application works only with');
$I->seeElement('#frameset-vilesci');

$I->wantTo('and now over index.ci.php');
$I->amOnPage('/index.ci.php');
$I->See('This application works only with');
$I->seeElement('#frameset-vilesci');

$I->wantTo('test the top menu');
$I->amOnPage('/vilesci/top.php');
$I->seeElement('.logo');

$I->wantTo('test the left nav-frame');
$I->amOnPage('/vilesci/left.php');
$I->seeElement('.left_nav');

$I->wantTo('test the main-frame');
$I->amOnPage('/vilesci/main.php');
$I->seeElement('img');
?>

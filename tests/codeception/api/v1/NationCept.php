<?php

$I = new ApiTester($scenario);
$I->wantTo('Test API call v1/nation/All');
$I->amHttpAuthenticated("wu11e001", "1Q2W3E4R");
$I->haveHttpHeader('FHC-API-KEY', 'testapikey@fhcomplete.org');
$I->sendGET('v1/nation/All');
$I->seeResponseCodeIs(200);
$I->seeResponseIsJson();
$I->seeResponseContainsJson([
	'success' => TRUE,
	'message' => 'Nation found']);

$I->sendGET('v1/nation/FederalState');
$I->seeResponseCodeIs(200);
$I->seeResponseIsJson();
$I->seeResponseContainsJson([
	'success' => TRUE,
	'message' => 'Bundesland found']);
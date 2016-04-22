<?php

$I = new ApiTester($scenario);
$I->wantTo('Test the HTTP basic autentication whith HTTP GET and POST method and the API Keys');
$I->amHttpAuthenticated("wu11e001", "1Q2W3E4R");
$I->haveHttpHeader('FHC-API-KEY', 'testapikey@fhcomplete.org');
$I->sendGET('Test/test');
$I->seeResponseCodeIs(200);
$I->seeResponseIsJson();
$I->seeResponseContainsJson([
	'success' => true,
	'message' => 'API HTTP GET call test succeed']);

$I->sendPOST('Test/test');
$I->seeResponseCodeIs(200);
$I->seeResponseIsJson();
$I->seeResponseContainsJson([
	'success' => true,
	'message' => 'API HTTP POST call test succeed']);
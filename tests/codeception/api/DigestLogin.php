<?php

$I = new ApiTester($scenario);
$I->wantTo('Test the HTTP digest autentication when calling an API');
$I->haveHttpHeader('FHC-API-KEY', 'testapikey@fhcomplete.org');
$I->sendGET('v1/Test/test');
$I->seeResponseCodeIs(200);
$I->seeResponseIsJson();
$I->seeResponseContainsJson([
	'success' => true,
	'message' => 'API HTTP GET call test succeed']);
$I->sendPOST('v1/Test/test');
$I->seeResponseCodeIs(200);
$I->seeResponseIsJson();
$I->seeResponseContainsJson([
	'success' => true,
	'message' => 'API HTTP POST call test succeed']);
<?php

$I = new ApiTester($scenario);
$I->wantTo('Test API call v1/person/person/Person by person_id');
$I->amHttpAuthenticated("wu11e001", "1Q2W3E4R");
$I->haveHttpHeader('FHC-API-KEY', 'testapikey@fhcomplete.org');
$I->sendGET('v1/person/person/Person', array('person_id' => 62788));
$I->seeResponseCodeIs(200);
$I->seeResponseIsJson();
$I->seeResponseContainsJson([
	'success' => TRUE,
	'message' => 'Person found']);

$I->sendGET('v1/person/person/Person', array('code' => 'bd94ef5d5a'));
$I->seeResponseCodeIs(200);
$I->seeResponseIsJson();
$I->seeResponseContainsJson([
	'success' => TRUE,
	'message' => 'Person found']);

$I->sendGET('v1/person/person/Person', array('code' => 'bd94ef5d5a', 'email' => '12351235'));
$I->seeResponseCodeIs(200);
$I->seeResponseIsJson();
$I->seeResponseContainsJson([
	'success' => TRUE,
	'message' => 'Person found']);
<?php

$I = new ApiTester($scenario);
$I->wantTo('Test API call v1/person/person/Person');
$I->amHttpAuthenticated("admin", "1q2w3");
$I->haveHttpHeader('FHC-API-KEY', 'testapikey@fhcomplete.org');
$I->sendGET('v1/person/person/Person', array('person_id' => 3));
$I->seeResponseCodeIs(200);
$I->seeResponseIsJson();
$I->seeResponseContainsJson([
	'success' => TRUE,
	'message' => 'Person found']);

$I->sendGET('v1/person/person/Person', array('code' => '01234567A'));
$I->seeResponseCodeIs(200);
$I->seeResponseIsJson();
$I->seeResponseContainsJson([
	'success' => TRUE,
	'message' => 'Person found']);

$I->sendGET('v1/person/person/Person', array('code' => '01234567A', 'email' => 'mckenzie.vicenta@calva.dev'));
$I->seeResponseCodeIs(200);
$I->seeResponseIsJson();
$I->seeResponseContainsJson([
	'success' => TRUE,
	'message' => 'Person found']);
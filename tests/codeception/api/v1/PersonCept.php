<?php

$I = new ApiTester($scenario);
$I->wantTo('test the Person code');
$I->amHttpAuthenticated("admin", "1q2w3");
$I->haveHttpHeader('FHC-API-KEY', 'testapikey@fhcomplete.org');
$I->sendGET('v1/person/Person/person?code=4ffd30cc58718');
$I->seeResponseCodeIs(200);
$I->seeResponseIsJson();
$I->seeResponseContainsJson([
    'person_id' => '3',
    'nachname' => 'McKenzie']);

$I->sendGET('v1/person/person/Person', array('code' => 'bd94ef5d5a'));
$I->seeResponseCodeIs(200);
$I->seeResponseIsJson();
$I->seeResponseContainsJson([
	'success' => false,
	'message' => 'Person not found']);

/*$I->sendGET('v1/person/person/Person', array('code' => 'bd94ef5d5a', 'email' => '12351235'));
$I->seeResponseCodeIs(200);
$I->seeResponseIsJson();
$I->seeResponseContainsJson([
	'success' => TRUE,
	'message' => 'Person found']);*/

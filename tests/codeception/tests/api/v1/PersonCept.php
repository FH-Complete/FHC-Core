<?php

$I = new ApiTester($scenario);
$I->wantTo('test the Person id');
$I->amHttpAuthenticated("admin", "1q2w3");
$I->haveHttpHeader('FHC-API-KEY', 'testapikey@fhcomplete.org');
$I->sendGET('v1/person/person/Person', array('person_id' => 3));
$I->seeResponseCodeIs(200);
$I->seeResponseIsJson();
$I->seeResponseContainsJson([
    'person_id' => '3',
    'nachname' => 'McKenzie']);

$I->sendGET('v1/person/person/Person', array('code' => '01234567B'));
$I->wantTo('test the Person code');
$I->haveHttpHeader('FHC-API-KEY', 'testapikey@fhcomplete.org');
$I->seeResponseCodeIs(200);
$I->seeResponseIsJson();
$I->seeResponseContainsJson([
    'person_id' => '4',
    'nachname' => 'Wilderman']);

$I->wantTo('test the Person not found');
$I->sendGET('v1/person/person/Person', array('code' => '12345'));
$I->seeResponseCodeIs(200);
$I->seeResponseIsJson();
$I->seeResponseContainsJson([
	'error' => 0,
	'retval' => array()]);

$I->sendGET('v1/person/person/Person', array('code' => '01234567C', 'email' => 'harvey.joshuah@calva.dev'));
$I->seeResponseCodeIs(200);
$I->seeResponseIsJson();
$I->seeResponseContainsJson([
    'person_id' => '5',
    'nachname' => 'Harvey']);
<?php

$I = new ApiTester($scenario);
$I->wantTo('Test API call v1/organisation/studiensemester studiensemester and nextStudiensemester');
$I->amHttpAuthenticated("admin", "1q2w3");
$I->haveHttpHeader('FHC-API-KEY', 'testapikey@fhcomplete.org');

$I->sendGET('v1/organisation/studiensemester/Studiensemester', array('studiensemester_kurzbz' => 'WS2016'));
$I->seeResponseCodeIs(200);
$I->seeResponseIsJson();
$I->seeResponseContainsJson(['error' => 0]);

$I->sendGET('v1/organisation/studiensemester/NextStudiensemester');
$I->seeResponseCodeIs(200);
$I->seeResponseIsJson();
$I->seeResponseContainsJson(['error' => 0]);

$I->sendGET('v1/organisation/studiensemester/NextStudiensemester', array('art' => 'WS'));
$I->seeResponseCodeIs(200);
$I->seeResponseIsJson();
$I->seeResponseContainsJson(['error' => 0]);
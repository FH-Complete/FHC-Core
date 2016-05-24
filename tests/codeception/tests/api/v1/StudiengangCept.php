<?php

$I = new ApiTester($scenario);
$I->wantTo('Test API call v1/organisation/studiengang Studiengang and AllForBewerbung');
$I->amHttpAuthenticated("admin", "1q2w3");
$I->haveHttpHeader('FHC-API-KEY', 'testapikey@fhcomplete.org');

$I->sendGET('v1/organisation/studiengang/Studiengang');
$I->seeResponseCodeIs(200);
$I->seeResponseIsJson();
$I->seeResponseContainsJson(['error' => 0]);

$I->sendGET('v1/organisation/studiengang/AllForBewerbung');
$I->seeResponseCodeIs(200);
$I->seeResponseIsJson();
$I->seeResponseContainsJson(['error' => 0]);
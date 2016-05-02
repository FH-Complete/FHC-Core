<?php

$I = new ApiTester($scenario);
$I->wantTo('Test API call v1/lehre/studiengang/AllForBewerbung');
$I->amHttpAuthenticated("admin", "1q2w3");
$I->haveHttpHeader('FHC-API-KEY', 'testapikey@fhcomplete.org');
$I->sendGET('v1/lehre/studiengang/AllForBewerbung');
$I->seeResponseCodeIs(200);
$I->seeResponseIsJson();
$I->seeResponseContainsJson([
	'success' => TRUE,
	'message' => 'Courses found']);
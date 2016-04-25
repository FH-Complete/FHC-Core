<?php

$I = new ApiTester($scenario);
$I->wantTo('Test API call v1/lehre/studienplan/Studienplaene');
$I->amHttpAuthenticated("admin", "1q2w3");
$I->haveHttpHeader('FHC-API-KEY', 'testapikey@fhcomplete.org');
$I->sendGET('v1/lehre/studienplan/Studienplaene', array('studiengang_kz' => 257));
$I->seeResponseCodeIs(200);
$I->seeResponseIsJson();
$I->seeResponseContainsJson([
	'success' => TRUE,
	'message' => 'Plan found']);
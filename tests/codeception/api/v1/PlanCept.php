<?php

$I = new ApiTester($scenario);
$I->wantTo('Test API call v1/studies/plan/Plan');
$I->amHttpAuthenticated("wu11e001", "1Q2W3E4R");
$I->haveHttpHeader('FHC-API-KEY', 'testapikey@fhcomplete.org');
$I->sendGET('v1/studies/plan/Plan', array('studiengang_kz' => 257));
$I->seeResponseCodeIs(200);
$I->seeResponseIsJson();
$I->seeResponseContainsJson([
	'success' => TRUE,
	'message' => 'Plan found']);
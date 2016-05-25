<?php

$I = new ApiTester($scenario);
$I->wantTo('Test API call v1/codex/orgform Orgform and All');
$I->amHttpAuthenticated("admin", "1q2w3");
$I->haveHttpHeader('FHC-API-KEY', 'testapikey@fhcomplete.org');

$I->sendGET('v1/codex/orgform/Orgform', array('orgform_kurzbz' => 'VZ'));
$I->seeResponseCodeIs(200);
$I->seeResponseIsJson();
$I->seeResponseContainsJson(['error' => 0]);

$I->sendGET('v1/codex/orgform/All');
$I->seeResponseCodeIs(200);
$I->seeResponseIsJson();
$I->seeResponseContainsJson(['error' => 0]);
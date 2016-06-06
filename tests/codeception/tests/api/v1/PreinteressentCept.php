<?php

$I = new ApiTester($scenario);
$I->wantTo('Test API call v1/crm/preinteressent Preinteressent and PreinteressentByPersonID');
$I->amHttpAuthenticated("admin", "1q2w3");
$I->haveHttpHeader('FHC-API-KEY', 'testapikey@fhcomplete.org');

$I->sendGET('v1/crm/preinteressent/Preinteressent', array('preinteressent_id' => 1));
$I->seeResponseCodeIs(200);
$I->seeResponseIsJson();
$I->seeResponseContainsJson(['error' => 0]);

$I->sendGET('v1/crm/preinteressent/PreinteressentByPersonID', array('person_id' => 3));
$I->seeResponseCodeIs(200);
$I->seeResponseIsJson();
$I->seeResponseContainsJson(['error' => 0]);
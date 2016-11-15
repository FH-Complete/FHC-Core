<?php

$I = new ApiTester($scenario);
$I->wantTo("Test API call v1/Test/test");
$I->amHttpAuthenticated("admin", "1q2w3");
$I->haveHttpHeader("FHC-API-KEY", "testapikey@fhcomplete.org");

$I->sendGET("v1/Test/test");
$I->seeResponseCodeIs(200);
$I->seeResponseIsJson();
$I->seeResponseContainsJson(["success" => true]);
$I->wait();
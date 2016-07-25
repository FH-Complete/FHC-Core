<?php

$I = new ApiTester($scenario);
$I->wantTo("Test API call v1/system/Message/Message");
$I->amHttpAuthenticated("admin", "1q2w3");
$I->haveHttpHeader("FHC-API-KEY", "testapikey@fhcomplete.org");

$I->sendGET("v1/system/Message/Message", array("person_id" => "0", "all" => "0", "uid" => "0", "all" => "0", "token" => "0"));
$I->seeResponseCodeIs(200);
$I->seeResponseIsJson();
$I->seeResponseContainsJson(["error" => 0]);
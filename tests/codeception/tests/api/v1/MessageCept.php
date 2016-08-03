<?php

$I = new ApiTester($scenario);
$I->wantTo("Test API call v1/system/message/MessagesByPersonID");
$I->amHttpAuthenticated("admin", "1q2w3");
$I->haveHttpHeader("FHC-API-KEY", "testapikey@fhcomplete.org");

$I->sendGET("v1/system/message/MessagesByPersonID", array("person_id" => "1"));
$I->seeResponseCodeIs(200);
$I->seeResponseIsJson();
$I->seeResponseContainsJson(["error" => 0]);
$I->wait();

$I->sendGET("v1/system/message/MessagesByUID", array("uid" => "mckenzie"));
$I->seeResponseCodeIs(200);
$I->seeResponseIsJson();
$I->seeResponseContainsJson(["error" => 0]);
$I->wait();

$I->sendGET("v1/system/message/MessagesByToken", array("token" => "token"));
$I->seeResponseCodeIs(200);
$I->seeResponseIsJson();
$I->seeResponseContainsJson(["error" => 0]);
$I->wait();
<?php

$I = new ApiTester($scenario);
$I->wantTo("Test API call v1/person/Person/: Person CheckBewerbung");
$I->amHttpAuthenticated("admin", "1q2w3");
$I->haveHttpHeader("FHC-API-KEY", "testapikey@fhcomplete.org");
$I->sendGET("v1/person/Person/Person", array("person_id" => "1", "code" => "1", "email" => "1"));
$I->seeResponseCodeIs(200);
$I->seeResponseIsJson();
$I->seeResponseContainsJson(["error" => 0]);
$I->wait();
$I->sendGET("v1/person/Person/CheckBewerbung", array("email" => "1", "studiensemester_kurzbz" => "1"));
$I->seeResponseCodeIs(200);
$I->seeResponseIsJson();
$I->seeResponseContainsJson(["error" => 0]);
$I->wait();

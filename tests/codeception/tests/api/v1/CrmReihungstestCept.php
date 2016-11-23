<?php

$I = new ApiTester($scenario);
$I->wantTo("Test API call v1/crm/Reihungstest/: Reihungstest ByStudiengangStudiensemester ReihungstestByPersonID");
$I->amHttpAuthenticated("admin", "1q2w3");
$I->haveHttpHeader("FHC-API-KEY", "testapikey@fhcomplete.org");
$I->sendGET("v1/crm/Reihungstest/Reihungstest", array("reihungstest_id" => "1"));
$I->seeResponseCodeIs(200);
$I->seeResponseIsJson();
$I->seeResponseContainsJson(["error" => 0]);
$I->wait();
$I->sendGET("v1/crm/Reihungstest/ByStudiengangStudiensemester", array("studiengang_kz" => "1", "studiensemester_kurzbz" => "1", "available" => "1"));
$I->seeResponseCodeIs(200);
$I->seeResponseIsJson();
$I->seeResponseContainsJson(["error" => 0]);
$I->wait();
$I->sendGET("v1/crm/Reihungstest/ReihungstestByPersonID", array("person_id" => "1", "available" => "1"));
$I->seeResponseCodeIs(200);
$I->seeResponseIsJson();
$I->seeResponseContainsJson(["error" => 0]);
$I->wait();

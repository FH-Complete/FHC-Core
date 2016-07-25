<?php

$I = new ApiTester($scenario);
$I->wantTo("Test API call v1/crm/Reihungstest/Reihungstest");
$I->amHttpAuthenticated("admin", "1q2w3");
$I->haveHttpHeader("FHC-API-KEY", "testapikey@fhcomplete.org");

$I->sendGET("v1/crm/Reihungstest/Reihungstest", array("reihungstest_id" => "0", "studiengang_kz" => "0", "studiensemester_kurzbz" => "0", "person_id" => "0"));
$I->seeResponseCodeIs(200);
$I->seeResponseIsJson();
$I->seeResponseContainsJson(["error" => 0]);
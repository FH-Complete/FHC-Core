<?php

$I = new ApiTester($scenario);
$I->wantTo("Test API call v1/organisation/Studienplan/: Studienplan Studienplaene StudienplaeneFromSem");
$I->amHttpAuthenticated("admin", "1q2w3");
$I->haveHttpHeader("FHC-API-KEY", "testapikey@fhcomplete.org");
$I->sendGET("v1/organisation/Studienplan/Studienplan", array("studienplan_id" => "1"));
$I->seeResponseCodeIs(200);
$I->seeResponseIsJson();
$I->seeResponseContainsJson(["error" => 0]);
$I->wait();
$I->sendGET("v1/organisation/Studienplan/Studienplaene", array("studiengang_kz" => "1"));
$I->seeResponseCodeIs(200);
$I->seeResponseIsJson();
$I->seeResponseContainsJson(["error" => 0]);
$I->wait();
$I->sendGET("v1/organisation/Studienplan/StudienplaeneFromSem", array("studiengang_kz" => "1", "studiensemester_kurzbz" => "1", "ausbildungssemester" => "1", "orgform_kurzbz" => "1"));
$I->seeResponseCodeIs(200);
$I->seeResponseIsJson();
$I->seeResponseContainsJson(["error" => 0]);
$I->wait();

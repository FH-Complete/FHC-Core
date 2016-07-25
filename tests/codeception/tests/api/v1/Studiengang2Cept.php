<?php

$I = new ApiTester($scenario);
$I->wantTo("Test API call v1/organisation/Studiengang2/Studiengang2");
$I->amHttpAuthenticated("admin", "1q2w3");
$I->haveHttpHeader("FHC-API-KEY", "testapikey@fhcomplete.org");

$I->sendGET("v1/organisation/Studiengang2/Studiengang2", array("studiengang_kz" => "0", "studiensemester_kurzbz" => "0", "ausbildungssemester" => "0", "aktiv" => "0", "onlinebewerbung" => "0"));
$I->seeResponseCodeIs(200);
$I->seeResponseIsJson();
$I->seeResponseContainsJson(["error" => 0]);
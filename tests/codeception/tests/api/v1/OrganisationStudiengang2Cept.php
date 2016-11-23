<?php

$I = new ApiTester($scenario);
$I->wantTo("Test API call v1/organisation/Studiengang2/: Studiengang AllForBewerbung StudiengangStudienplan StudiengangBewerbung");
$I->amHttpAuthenticated("admin", "1q2w3");
$I->haveHttpHeader("FHC-API-KEY", "testapikey@fhcomplete.org");
$I->sendGET("v1/organisation/Studiengang2/Studiengang", array("studiengang_kz" => "1"));
$I->seeResponseCodeIs(200);
$I->seeResponseIsJson();
$I->seeResponseContainsJson(["error" => 0]);
$I->wait();
$I->sendGET("v1/organisation/Studiengang2/AllForBewerbung", array());
$I->seeResponseCodeIs(200);
$I->seeResponseIsJson();
$I->seeResponseContainsJson(["error" => 0]);
$I->wait();
$I->sendGET("v1/organisation/Studiengang2/StudiengangStudienplan", array("studiensemester_kurzbz" => "1", "ausbildungssemester" => "1", "aktiv" => "1", "onlinebewerbung" => "1"));
$I->seeResponseCodeIs(200);
$I->seeResponseIsJson();
$I->seeResponseContainsJson(["error" => 0]);
$I->wait();
$I->sendGET("v1/organisation/Studiengang2/StudiengangBewerbung", array());
$I->seeResponseCodeIs(200);
$I->seeResponseIsJson();
$I->seeResponseContainsJson(["error" => 0]);
$I->wait();

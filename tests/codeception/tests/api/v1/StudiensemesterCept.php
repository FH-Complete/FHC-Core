<?php

$I = new ApiTester($scenario);
$I->wantTo("Test API call to all v1/organisation/studiensemester methods");
$I->amHttpAuthenticated("admin", "1q2w3");
$I->haveHttpHeader("FHC-API-KEY", "testapikey@fhcomplete.org");

$I->sendGET("v1/organisation/studiensemester/Studiensemester", array("studiensemester_kurzbz" => "WS2016"));
$I->seeResponseCodeIs(200);
$I->seeResponseIsJson();
$I->seeResponseContainsJson(["error" => 0]);
$I->wait();

$I->sendGET("v1/organisation/studiensemester/NextStudiensemester");
$I->seeResponseCodeIs(200);
$I->seeResponseIsJson();
$I->seeResponseContainsJson(["error" => 0]);
$I->wait();

$I->sendGET("v1/organisation/studiensemester/NextStudiensemester", array("art" => "WS"));
$I->seeResponseCodeIs(200);
$I->seeResponseIsJson();
$I->seeResponseContainsJson(["error" => 0]);
$I->wait();

$I->sendGET("v1/organisation/studiensemester/All");
$I->seeResponseCodeIs(200);
$I->seeResponseIsJson();
$I->seeResponseContainsJson(["error" => 0]);
$I->wait();

$I->sendGET("v1/organisation/studiensemester/Akt");
$I->seeResponseCodeIs(200);
$I->seeResponseIsJson();
$I->seeResponseContainsJson(["error" => 0]);
$I->wait();

$I->sendGET("v1/organisation/studiensemester/AktNext", array("semester" => "1"));
$I->seeResponseCodeIs(200);
$I->seeResponseIsJson();
$I->seeResponseContainsJson(["error" => 0]);
$I->wait();

$I->sendGET("v1/organisation/studiensemester/AktNext", array("semester" => "2"));
$I->seeResponseCodeIs(200);
$I->seeResponseIsJson();
$I->seeResponseContainsJson(["error" => 0]);
$I->wait();

$I->sendGET("v1/organisation/studiensemester/LastOrAktSemester");
$I->seeResponseCodeIs(200);
$I->seeResponseIsJson();
$I->seeResponseContainsJson(["error" => 0]);
$I->wait();

$I->sendGET("v1/organisation/studiensemester/LastOrAktSemester", array("days" => "1024"));
$I->seeResponseCodeIs(200);
$I->seeResponseIsJson();
$I->seeResponseContainsJson(["error" => 0]);
$I->wait();

$I->sendGET("v1/organisation/studiensemester/NextFrom", array("studiensemester_kurzbz" => "WS2015"));
$I->seeResponseCodeIs(200);
$I->seeResponseIsJson();
$I->seeResponseContainsJson(["error" => 0]);
$I->wait();

$I->sendGET("v1/organisation/studiensemester/Previous");
$I->seeResponseCodeIs(200);
$I->seeResponseIsJson();
$I->seeResponseContainsJson(["error" => 0]);
$I->wait();

$I->sendGET("v1/organisation/studiensemester/Nearest", array("studiensemester_kurzbz" => "WS2015"));
$I->seeResponseCodeIs(200);
$I->seeResponseIsJson();
$I->seeResponseContainsJson(["error" => 0]);
$I->wait();

$I->sendGET("v1/organisation/studiensemester/Finished");
$I->seeResponseCodeIs(200);
$I->seeResponseIsJson();
$I->seeResponseContainsJson(["error" => 0]);
$I->wait();

$I->sendGET("v1/organisation/studiensemester/Finished", array("limit" => "3"));
$I->seeResponseCodeIs(200);
$I->seeResponseIsJson();
$I->seeResponseContainsJson(["error" => 0]);
$I->wait();

$I->sendGET("v1/organisation/studiensemester/Timestamp", array("studiensemester_kurzbz" => "WS2015"));
$I->seeResponseCodeIs(200);
$I->seeResponseIsJson();
$I->seeResponseContainsJson(["error" => 0]);
$I->wait();
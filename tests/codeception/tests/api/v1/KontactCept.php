<?php

$I = new ApiTester($scenario);
$I->wantTo("Test API call v1/person/kontakt/ kontakt, KontaktByPersonID and KontaktByPersonIDKontaktTyp");
$I->amHttpAuthenticated("admin", "1q2w3");
$I->haveHttpHeader("FHC-API-KEY", "testapikey@fhcomplete.org");

$I->sendGET("v1/person/kontakt/kontakt", array("kontakt_id" => 1));
$I->seeResponseCodeIs(200);
$I->seeResponseIsJson();
$I->seeResponseContainsJson(["error" => 0]);
$I->wait();

$I->sendGET("v1/person/kontakt/KontaktByPersonID", array("person_id" => 3));
$I->seeResponseCodeIs(200);
$I->seeResponseIsJson();
$I->seeResponseContainsJson(["error" => 0]);
$I->wait();

$I->sendGET("v1/person/kontakt/KontaktByPersonIDKontaktTyp", array("person_id" => 3, "kontakttyp" => "email"));
$I->seeResponseCodeIs(200);
$I->seeResponseIsJson();
$I->seeResponseContainsJson(["error" => 0]);
$I->wait();
<?php

$I = new ApiTester($scenario);
$I->wantTo("Test API call v1/crm/Akte/: Akte Akten AktenAccepted");
$I->amHttpAuthenticated("admin", "1q2w3");
$I->haveHttpHeader("FHC-API-KEY", "testapikey@fhcomplete.org");
$I->sendGET("v1/crm/Akte/Akte", array("akte_id" => "1"));
$I->seeResponseCodeIs(200);
$I->seeResponseIsJson();
$I->seeResponseContainsJson(["error" => 0]);
$I->wait();
$I->sendGET("v1/crm/Akte/Akten", array("person_id" => "1", "dokument_kurzbz" => "1", "stg_kz" => "1", "prestudent_id" => "1"));
$I->seeResponseCodeIs(200);
$I->seeResponseIsJson();
$I->seeResponseContainsJson(["error" => 0]);
$I->wait();
$I->sendGET("v1/crm/Akte/AktenAccepted", array("person_id" => "1", "dokument_kurzbz" => "1"));
$I->seeResponseCodeIs(200);
$I->seeResponseIsJson();
$I->seeResponseContainsJson(["error" => 0]);
$I->wait();

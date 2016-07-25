<?php

$I = new ApiTester($scenario);
$I->wantTo("Test API call v1/crm/Dokumentstudiengang/Dokumentstudiengang");
$I->amHttpAuthenticated("admin", "1q2w3");
$I->haveHttpHeader("FHC-API-KEY", "testapikey@fhcomplete.org");

$I->sendGET("v1/crm/Dokumentstudiengang/Dokumentstudiengang", array("studiengang_kz" => "0", "dokument_kurzbz" => "0", "studiengang_kz" => "0", "onlinebewerbung" => "0", "pflicht" => "0"));
$I->seeResponseCodeIs(200);
$I->seeResponseIsJson();
$I->seeResponseContainsJson(["error" => 0]);
<?php

$I = new ApiTester($scenario);
$I->wantTo("Test API call v1/crm/Dokumentstudiengang/: Dokumentstudiengang DokumentstudiengangByStudiengang_kz");
$I->amHttpAuthenticated("admin", "1q2w3");
$I->haveHttpHeader("FHC-API-KEY", "testapikey@fhcomplete.org");
$I->sendGET("v1/crm/Dokumentstudiengang/Dokumentstudiengang", array("studiengang_kz" => "1", "dokument_kurzbz" => "1"));
$I->seeResponseCodeIs(200);
$I->seeResponseIsJson();
$I->seeResponseContainsJson(["error" => 0]);
$I->wait();
$I->sendGET("v1/crm/Dokumentstudiengang/DokumentstudiengangByStudiengang_kz", array("studiengang_kz" => "1", "onlinebewerbung" => "1", "pflicht" => "1", "nachreichbar" => "1"));
$I->seeResponseCodeIs(200);
$I->seeResponseIsJson();
$I->seeResponseContainsJson(["error" => 0]);
$I->wait();

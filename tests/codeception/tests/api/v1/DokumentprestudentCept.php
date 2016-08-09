<?php

$I = new ApiTester($scenario);
$I->wantTo("Test API call v1/crm/Dokumentprestudent/Dokumentprestudent");
$I->amHttpAuthenticated("admin", "1q2w3");
$I->haveHttpHeader("FHC-API-KEY", "testapikey@fhcomplete.org");

$I->sendGET("v1/crm/Dokumentprestudent/Dokumentprestudent", array("prestudent_id" => "0", "dokument_kurzbz" => "0"));
$I->seeResponseCodeIs(200);
$I->seeResponseIsJson();
$I->seeResponseContainsJson(["error" => 0]);
$I->wait();
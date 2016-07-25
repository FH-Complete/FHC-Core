<?php

$I = new ApiTester($scenario);
$I->wantTo("Test API call v1/organisation/Statistik/Statistik");
$I->amHttpAuthenticated("admin", "1q2w3");
$I->haveHttpHeader("FHC-API-KEY", "testapikey@fhcomplete.org");

$I->sendGET("v1/organisation/Statistik/Statistik", array("statistik_kurzbz" => "0", "order" => "0"));
$I->seeResponseCodeIs(200);
$I->seeResponseIsJson();
$I->seeResponseContainsJson(["error" => 0]);
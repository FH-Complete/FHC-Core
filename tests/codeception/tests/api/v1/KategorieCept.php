<?php

$I = new ApiTester($scenario);
$I->wantTo("Test API call v1/testtool/Kategorie/Kategorie");
$I->amHttpAuthenticated("admin", "1q2w3");
$I->haveHttpHeader("FHC-API-KEY", "testapikey@fhcomplete.org");

$I->sendGET("v1/testtool/Kategorie/Kategorie", array("kategorie_kurzbz" => "0"));
$I->seeResponseCodeIs(200);
$I->seeResponseIsJson();
$I->seeResponseContainsJson(["error" => 0]);
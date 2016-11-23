<?php

$I = new ApiTester($scenario);
$I->wantTo("Test API call v1/organisation/Lehrverband/: Lehrverband");
$I->amHttpAuthenticated("admin", "1q2w3");
$I->haveHttpHeader("FHC-API-KEY", "testapikey@fhcomplete.org");
$I->sendGET("v1/organisation/Lehrverband/Lehrverband", array("gruppe" => "1", "verband" => "1", "semester" => "1", "studiengang_kz" => "1"));
$I->seeResponseCodeIs(200);
$I->seeResponseIsJson();
$I->seeResponseContainsJson(["error" => 0]);
$I->wait();

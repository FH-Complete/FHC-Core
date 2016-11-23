<?php

$I = new ApiTester($scenario);
$I->wantTo("Test API call v1/codex/Lehrform/: Lehrform");
$I->amHttpAuthenticated("admin", "1q2w3");
$I->haveHttpHeader("FHC-API-KEY", "testapikey@fhcomplete.org");
$I->sendGET("v1/codex/Lehrform/Lehrform", array("lehrform_kurzbz" => "1"));
$I->seeResponseCodeIs(200);
$I->seeResponseIsJson();
$I->seeResponseContainsJson(["error" => 0]);
$I->wait();

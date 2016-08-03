<?php

$I = new ApiTester($scenario);
$I->wantTo("Test API call v1/ressource/Zeitwunsch/Zeitwunsch");
$I->amHttpAuthenticated("admin", "1q2w3");
$I->haveHttpHeader("FHC-API-KEY", "testapikey@fhcomplete.org");

$I->sendGET("v1/ressource/Zeitwunsch/Zeitwunsch", array("tag" => "0", "mitarbeiter_uid" => "0", "stunde" => "0"));
$I->seeResponseCodeIs(200);
$I->seeResponseIsJson();
$I->seeResponseContainsJson(["error" => 0]);
$I->wait();
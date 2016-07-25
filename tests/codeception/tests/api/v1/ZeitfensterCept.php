<?php

$I = new ApiTester($scenario);
$I->wantTo("Test API call v1/ressource/Zeitfenster/Zeitfenster");
$I->amHttpAuthenticated("admin", "1q2w3");
$I->haveHttpHeader("FHC-API-KEY", "testapikey@fhcomplete.org");

$I->sendGET("v1/ressource/Zeitfenster/Zeitfenster", array("wochentag" => "0", "studiengang_kz" => "0", "ort_kurzbz" => "0", "stunde" => "0"));
$I->seeResponseCodeIs(200);
$I->seeResponseIsJson();
$I->seeResponseContainsJson(["error" => 0]);
<?php

$I = new ApiTester($scenario);
$I->wantTo("Test API call v1/education/Projektbetreuer/Projektbetreuer");
$I->amHttpAuthenticated("admin", "1q2w3");
$I->haveHttpHeader("FHC-API-KEY", "testapikey@fhcomplete.org");

$I->sendGET("v1/education/Projektbetreuer/Projektbetreuer", array("betreuerart_kurzbz" => "0", "projektarbeit_id" => "0", "person_id" => "0"));
$I->seeResponseCodeIs(200);
$I->seeResponseIsJson();
$I->seeResponseContainsJson(["error" => 0]);